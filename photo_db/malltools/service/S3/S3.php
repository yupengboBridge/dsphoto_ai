<?php

use Aws\S3\S3Client;

class S3{

    private $client = null;

    public function __construct(string $key,string $secret,string $region,string $version='latest'){
        try{
            $this->client = new S3Client([
                'version'     => $version,
                'region'      => $region,
                'credentials' => [
                    'key'    => $key,
                    'secret' => $secret
                ]
            ]);
        }catch (Exception $e){
            throw $e;
        }
    }

    public function listStepper($args=[],$callback){
        $results = $this->client->getPaginator('ListObjectsV2', $args);
        foreach ($results->search('Contents[].Key') as $key) {
            call_user_func($callback,$key);
        }
    }

    public function list($args=[]){
        return $this->client->listObjectsV2($args);
    }

    public function getBucketPaths($bucket,$prefix="",$isProcessedSkip=false,$isRecursion=false,$reset = false){
        static $folders = [];

        if($reset==true){
            $folders = [];
        }

        try {
            $result = $this->client->listObjectsV2([
                'Bucket'    => $bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
            ]);
            if (isset($result['CommonPrefixes'])) {
                foreach ($result['CommonPrefixes'] as $folder) {
                    $dir_name = rtrim($folder['Prefix'], '/');
                    if($isProcessedSkip){
                        if($dir_name != "processed"){
                            $folders[] = str_replace("//","/",str_replace("processed/","",$dir_name));
                        }
                    }else{
                        $folders[] = str_replace("//","/",str_replace("processed/","",$dir_name));
                    }

                    if($isRecursion==true){
                        $this->getBucketPaths($bucket,$folder['Prefix'],$isProcessedSkip,$isRecursion);
                    }
                }
                return $folders;
            } else {
                return [];
            }
        } catch (AwsException $e) {
            return [];
        }
    }

    public function moveObject($bucket,$sourceFolder,$is_deleted=false){
        $destinationFolder = 'processed/' . basename($sourceFolder) . '/';

        try {
            $result = $this->client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => $sourceFolder,
            ]);

            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $sourceKey = $object['Key'];
                    $ext = pathinfo($sourceKey, PATHINFO_EXTENSION);
                    if("EPS" == strtoupper($ext) 
                        || "AI" == strtoupper($ext)
                        || "TIFF" == strtoupper($ext)
                        || "TIF" == strtoupper($ext)
                        || "PSD" == strtoupper($ext)
                    )
                    {
                        continue;
                    }
                    $destinationKey = str_replace($sourceFolder, $destinationFolder, $sourceKey);
                    $this->client->copyObject([
                        'Bucket'     => $bucket,
                        'CopySource' => "{$bucket}/{$sourceKey}",
                        'Key'        => $destinationKey,
                    ]);
                    if($is_deleted==true){
                        $this->client->deleteObject([
                            'Bucket' => $bucket,
                            'Key'    => $sourceKey,
                        ]);
                    }
                }
            }

            $result_root = $this->client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => "",
                'Delimiter' => '/'
            ]);

            if (!empty($result_root['CommonPrefixes'])) {
                $commonPrefixes = $result_root['CommonPrefixes'];
                foreach ($commonPrefixes as $prefix) {
                    if($prefix['Prefix'] == $sourceFolder."/"){
                        if($is_deleted==true){
                            $this->client->deleteObject([
                                'Bucket' => $bucket,
                                'Key'    => $sourceFolder,
                            ]);
                        }
                    }
                }
            }

        } catch (AwsException $e) {}
    }

    public function restoreObject($bucket,$sourceFolder){
        $srcFolder = 'processed/' . basename($sourceFolder) . '/';
        $destinationFolder = '/'.basename($sourceFolder) . '/';

        try {
            $result = $this->client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => $srcFolder,
            ]);

            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $sourceKey = $object['Key'];
                    $destinationKey = str_replace("processed/", "", $sourceKey);
                    print($destinationKey."\n");
                    $this->client->copyObject([
                        'Bucket'     => $bucket,
                        'CopySource' => "{$bucket}/{$sourceKey}",
                        'Key'        => $destinationKey,
                    ]);
                }
            }

        } catch (AwsException $e) {}
    }

    public function get($args){
        return $this->client->getObject($args);
    }

    public function downloadAll($bucket,$dest,$prefix=""){
        $source = "s3://{$bucket}";
        if(!empty($prefix)){
            $source .= "/".$prefix;
        }
        $manager = new \Aws\S3\Transfer($this->client, $source, $dest);
        $manager->transfer();
    }

}
