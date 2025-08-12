<?php
// PHP 7.0 互換

// ULIZA の埋め込み用スクリプトが返す JS を解析し、params.src.poster を抽出します。

class UlizaPosterClient
{
    /** @var string */
    private $endpoint;

    public function __construct($endpoint = 'https://player-api.p.uliza.jp/v1/players/default-player/cread/hei?type=normal&name=')
    {
        $this->endpoint = $endpoint;
    }

    /**
     * name を指定してURLを組み立て、ページ本文を取得 → params を抽出 → poster を返す
     * 戻り値:
     * - 成功: ['ok'=>true, 'name'=>..., 'poster'=>..., 'posters'=>[...], 'sourceUrl'=>...]
     * - 失敗: ['ok'=>false, 'error'=>..., 'status'=>..., 'sourceUrl'=>..., 'bodySnippet'=>...]
     */
    public function fetchPoster($name)
    {
        $url = $this->buildUrl($name);
        list($status, $body, $err) = $this->httpGet($url);
        if ($err !== null) {
            return [
                'ok' => false,
                'error' => 'Network error: ' . $err,
                'status' => 502,
                'sourceUrl' => $url,
            ];
        }

        if ($status !== 200 || !is_string($body)) {
            return [
                'ok' => false,
                'error' => 'Upstream HTTP error',
                'status' => $status,
                'sourceUrl' => $url,
                'bodySnippet' => is_string($body) ? $this->snippet($body, 500) : null,
            ];
        }

        // 1) JSON を直接返す場合のフォールバック（稀）
        $asJson = json_decode($body, true);
        if (is_array($asJson)) {
            $params = $asJson;
        } else {
            // 2) JS 内の "var params = {...};" からオブジェクト部分を抜き出し → JSON 化
            $params = $this->extractParamsFromJavascript($body);
            if (!is_array($params)) {
                return [
                    'ok' => false,
                    'error' => 'Failed to parse params from JavaScript',
                    'status' => 502,
                    'sourceUrl' => $url,
                    'bodySnippet' => $this->snippet($body, 600),
                ];
            }
        }

        // ULIZA の埋め込みでは poster は params.src.poster に存在
        $poster = isset($params['src']) && isset($params['src']['poster']) ? $params['src']['poster'] : null;
        $posters = isset($params['src']['settings']['posterSlideShow']['posters']) && is_array($params['src']['settings']['posterSlideShow']['posters'])
            ? $params['src']['settings']['posterSlideShow']['posters']
            : [];

        if (!$poster) {
            return [
                'ok' => false,
                'error' => 'params.src.poster が見つかりません',
                'status' => 404,
                'sourceUrl' => $url,
            ];
        }

        return [
            'ok' => true,
            'name' => $name,
            'poster' => $poster,
            'posters' => $posters,
            'sourceUrl' => $url,
        ];
    }

    private function buildUrl($name)
    {
        $query = http_build_query([
            'type' => 'normal',
            'name' => $name,
        ]);
        return $this->endpoint . '?' . $query;
    }

    private function httpGet($url)
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return [0, null, 'Failed to initialize cURL'];
        }

        $headers = [
            'Accept: text/javascript, application/javascript, application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate',
            'Connection: close',
            'User-Agent: dsphoto_ai/1.0 (+fetch_uliza_poster.php)'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => ''
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = $errno ? curl_error($ch) : null;
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($errno) {
            return [0, null, $error ?: 'Unknown cURL error'];
        }

        return [$httpCode, $body, null];
    }

    /**
     * JS 本文から "var params = {...};" の { ... } 部分を波括弧対応で抽出し、JSON として decode する。
     */
    private function extractParamsFromJavascript($javascript)
    {
        // 'var params' の位置
        $idx = strpos($javascript, 'var params');
        if ($idx === false) {
            // フォールバック: 'params' の代入を探す
            $idx = strpos($javascript, 'params');
            if ($idx === false) return null;
        }

        // '=' の位置 → その後の最初の '{' を探す
        $eqPos = strpos($javascript, '=', $idx);
        if ($eqPos === false) return null;
        $braceStart = strpos($javascript, '{', $eqPos);
        if ($braceStart === false) {
            return null;
        }

        // 文字列/エスケープに注意して波括弧を対応させながら末尾 '}' を見つける
        $length = strlen($javascript);
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $escaped = false;
        $endPos = null;

        for ($i = $braceStart; $i < $length; $i++) {
            $ch = $javascript[$i];

            if ($inSingle) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === "\\") {
                    $escaped = true;
                } elseif ($ch === "'") {
                    $inSingle = false;
                }
                continue;
            }

            if ($inDouble) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === "\\") {
                    $escaped = true;
                } elseif ($ch === '"') {
                    $inDouble = false;
                }
                continue;
            }

            if ($ch === "'") { $inSingle = true; continue; }
            if ($ch === '"') { $inDouble = true; continue; }

            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $endPos = $i;
                    break;
                }
            }
        }

        if ($endPos === null) {
            return null;
        }

        $objectLike = substr($javascript, $braceStart, $endPos - $braceStart + 1);

        // JSON に寄せる: 末尾カンマを除去
        $jsonLike = $this->removeTrailingCommas($objectLike);

        // そのまま decode を試す
        $decoded = json_decode($jsonLike, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // シングルクォート文字列が混じるケースの簡易対応（完全ではないが多くのケースで有効）
        $jsonLike2 = preg_replace_callback("/\\'|'/u", function ($m) {
            if ($m[0] === "\\'") return "\\'"; // 既にエスケープされた \' はそのまま
            return '"';
        }, $jsonLike);
        $jsonLike2 = $this->removeTrailingCommas($jsonLike2);
        $decoded2 = json_decode($jsonLike2, true);
        if (is_array($decoded2)) {
            return $decoded2;
        }

        return null;
    }

    private function removeTrailingCommas($jsonLike)
    {
        // } や ] の直前にある余分なカンマを削除
        return preg_replace('/,\s*(\}|\])/m', '$1', $jsonLike);
    }

    private function snippet($text, $max)
    {
        if (!is_string($text)) return null;
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, (int)$max);
        }
        return substr($text, 0, (int)$max);
    }

    // 直接実行時のエントリポイント
    public static function handleDirectExecution()
    {
        $name = isset($_GET['name']) ? $_GET['name'] : null;
        if (PHP_SAPI === 'cli') {
            $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
            for ($i = 1; $i < count($argv); $i++) {
                if (strpos($argv[$i], '--name=') === 0) {
                    $name = substr($argv[$i], 7);
                    break;
                } elseif ($argv[$i] === '--name' && isset($argv[$i+1])) {
                    $name = $argv[$i+1];
                    break;
                }
            }
        }

        if (!is_string($name) || $name === '') {
            self::respondJson([
                'ok' => false,
                'error' => 'Missing required parameter: name',
                'usage' => [
                    'web' => 'GET ?name=content-YYYY-MM-DD-HH-MM-SS-xxx-xxxxxxxx',
                    'cli' => 'php fetch_uliza_poster.php --name=content-...'
                ]
            ], 400);
        }

        $client = new self();
        $result = $client->fetchPoster($name);
        $status = isset($result['ok']) && $result['ok'] ? 200 : (isset($result['status']) ? (int)$result['status'] : 500);
        self::respondJson($result, $status);
    }

    private static function respondJson(array $payload, $statusCode = 200)
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code((int)$statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
        exit(0);
    }
}

// 直接実行時のみ動作
if (realpath(__FILE__) === realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '')) {
    UlizaPosterClient::handleDirectExecution();
}


