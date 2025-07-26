<?php

class Img {

	private $image;
	private $imagePath;
	private $isCrop;
	public $cmykIccPath;
	public $srgbIccPath;

	public function __construct()
	{
		if (!extension_loaded('imagick')) {
			throw new CrashException('未インストールの拡張機能：imagick');
		}
		$this->image = new \Imagick();
		$this->isCrop = true;
	}

	public function load($imageFile){
		try {
			if(!is_file($imageFile)){
				throw new Exception('画像が存在しません：:'.$imageFile);
			}
			$this->image->readImage($imageFile);
		} catch (Exception $e) {
			throw $e;
		}
		$this->imagePath = $imageFile; // 保存路径以便后续操作
	}

	private function ratio($a, $b) {
		$_a = $a;
		$_b = $b;
		while ($_b != 0) {
			$remainder = $_a % $_b;
			$_a = $_b;
			$_b = $remainder;
		}
		$gcd = abs($_a);
		return ($a / $gcd)  . ':' . ($b / $gcd);
	}

	public function crop($width = 800, $height = 600) {
		try {
			$originalWidth = $this->image->getImageWidth();
			$originalHeight = $this->image->getImageHeight();

			if ($originalWidth == $width && $originalHeight == $height) {
				$this->isCrop = false;
				return;
			}

			$colorSpace = $this->image->getImageColorspace();

			// アスペクト比の判定
			if ($originalWidth > $originalHeight) {
				// 横長画像 → 高さ600にスケーリング後、中央800pxをクロップ（白ベタなし）
				$scale = $height / $originalHeight;
				$scaledWidth = (int)($originalWidth * $scale);
				$this->image->scaleImage(0, $height);

				// 中央800pxを切り抜く
				$xCrop = max(0, ($scaledWidth - $width) / 2);
				$this->image->cropImage($width, $height, $xCrop, 0);
			} elseif ($originalWidth < $originalHeight) {
				// 縦長画像 → 幅を保ちつつ高さ600にスケーリング、白ベタ追加
				$scale = $height / $originalHeight;
				$scaledWidth = (int)($originalWidth * $scale);
				$scaledHeight = $height;
				$this->image->scaleImage(0, $height);

				// 白背景のキャンバスを作成（中央配置）
				$x = max(0, ($width - $scaledWidth) / 2);
				$canvas = new \Imagick();
				$canvas->newImage($width, $height, 'white', 'jpg');

				// 色空間変換（CMYK対応）
				if ($colorSpace == \Imagick::COLORSPACE_CMYK) {
					$cmykProfilePath = $this->cmykIccPath;
					$srgbProfilePath = $this->srgbIccPath;
					$this->image->profileImage('*', null);
					$this->image->profileImage('icc', file_get_contents($cmykProfilePath));
					$this->image->profileImage('icc', file_get_contents($srgbProfilePath));
					$this->image->modulateImage(100, 110, 100);
					$this->image->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
				}

				$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, 0);
				$canvas->setImageColorspace(\Imagick::COLORSPACE_SRGB);
				$canvas->setImageFormat('jpeg');
				$this->image = $canvas;
			} else {
				// 正方形 or 比例画像 → フィットさせて中央白ベタ
				$scale = min($width / $originalWidth, $height / $originalHeight);
				$scaledWidth = (int)($originalWidth * $scale);
				$scaledHeight = (int)($originalHeight * $scale);
				$this->image->resizeImage($scaledWidth, $scaledHeight, \Imagick::FILTER_LANCZOS, 1, true);

				$x = max(0, ($width - $scaledWidth) / 2);
				$y = max(0, ($height - $scaledHeight) / 2);
				$canvas = new \Imagick();
				$canvas->newImage($width, $height, 'white', 'jpg');

				if ($colorSpace == \Imagick::COLORSPACE_CMYK) {
					$cmykProfilePath = $this->cmykIccPath;
					$srgbProfilePath = $this->srgbIccPath;
					$this->image->profileImage('*', null);
					$this->image->profileImage('icc', file_get_contents($cmykProfilePath));
					$this->image->profileImage('icc', file_get_contents($srgbProfilePath));
					$this->image->modulateImage(100, 110, 100);
					$this->image->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
				}

				$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);
				$canvas->setImageColorspace(\Imagick::COLORSPACE_SRGB);
				$canvas->setImageFormat('jpeg');
				$this->image = $canvas;
			}
		} catch (Exception $e) {
			throw $e;
		}
	}


	public function cropForLHAndLF($targetWidth = 800, $targetHeight = 600) {
		$originalWidth = $this->image->getImageWidth();
		$originalHeight = $this->image->getImageHeight();

		$originalRatio = $originalWidth / $originalHeight;
		$targetRatio = $targetWidth / $targetHeight;

		// アスペクト比を保ったまま縮小（規定を超える方を基準に）
		if ($originalRatio > $targetRatio) {
			// 横長：幅を800に、縦を比率で縮小
			$this->image->scaleImage($targetWidth, 0);
		} else {
			// 縦長：高さを600に、幅を比率で縮小
			$this->image->scaleImage(0, $targetHeight);
		}

		// 縮小後のサイズ取得
		$scaledWidth = $this->image->getImageWidth();
		$scaledHeight = $this->image->getImageHeight();

		// 白ベタキャンバス作成
		$canvas = new \Imagick();
		$canvas->newImage($targetWidth, $targetHeight, 'white', 'jpg');

		// 貼り付け位置（中央配置）
		$x = ($targetWidth - $scaledWidth) / 2;
		$y = ($targetHeight - $scaledHeight) / 2;

		// CMYK対応
		$colorSpace = $this->image->getImageColorspace();
		if ($colorSpace == \Imagick::COLORSPACE_CMYK) {
			$this->image->profileImage('*', null);
			$this->image->profileImage('icc', file_get_contents($this->cmykIccPath));
			$this->image->profileImage('icc', file_get_contents($this->srgbIccPath));
			$this->image->modulateImage(100, 110, 100);
			$this->image->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
		}

		// キャンバスに貼り付け（中央）
		$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);

		if ($colorSpace == \Imagick::COLORSPACE_CMYK) {
			$canvas->setImageColorspace(\Imagick::COLORSPACE_SRGB);
		}

		$this->image = $canvas;
		$this->isCrop = true;
	}

	// public function crop($width = 800, $height = 600) {
	// 	try{
	// 		// 获取原始图像的宽度和高度
	// 		$originalWidth = $this->image->getImageWidth();
	// 		$originalHeight = $this->image->getImageHeight();
	// 		if($originalWidth == $width && $originalHeight == $height){
	// 			$this->isCrop = false;
	// 			return;
	// 		}

	// 		$colorSpace = $this->image->getImageColorspace();

	// 		if($originalWidth >= $width || $originalHeight >= $height){
	// 			// 计算缩放比例
	// 			$scale = min($width / $originalWidth, $height / $originalHeight);
	// 			// 计算缩放后的尺寸
	// 			$scaledWidth = $originalWidth * $scale;
	// 			$scaledHeight = $originalHeight * $scale;

	// 			// 缩放图像
	// 			$this->image->resizeImage($scaledWidth, $scaledHeight, \Imagick::FILTER_LANCZOS, 1, true);
	// 			/*
	// 			if($originalWidth >= $width && $originalHeight >= $height) {
	// 				if ($this->ratio($originalWidth, $originalHeight) == "4:3") {
	// 					$this->isCrop = false;
	// 					return;
	// 				}
	// 			}
	// 			*/
	// 		}else{
	// 			// 判断宽高比，并按规则进行放大
	// 			if ($originalWidth > $originalHeight) {
	// 				// 计算缩放比例
	// 				$scale = $width / $originalWidth;
	// 				// 计算缩放后的尺寸
	// 				$scaledWidth = $width;
	// 				$scaledHeight = $originalHeight * $scale;

	// 				// 宽大于高，设置宽度为 800，高度自适应
	// 				$this->image->scaleImage($width, 0);
	// 			} else {
	// 				// 计算缩放比例
	// 				$scale = $height / $originalHeight;
	// 				// 计算缩放后的尺寸
	// 				$scaledWidth = $originalWidth * $scale;
	// 				$scaledHeight = $height;
	// 				// 高大于宽，设置高度为 600，宽度自适应
	// 				$this->image->scaleImage(0, $height);
	// 			}
	// 		}

	// 		// 元の画像のサイズに合わせて、中央に配置
	// 		$x = max(0, ($width - $scaledWidth) / 2);
	// 		$y = max(0, ($height - $scaledHeight) / 2);

	// 		$canvas = new \Imagick();
	// 		$canvas->newImage($width, $height , 'white', 'jpg'); // 设置背景色为白色

	// 		if($colorSpace==\Imagick::COLORSPACE_CMYK){
	// 			// CMYKからsRGBに変換する際のICCプロファイルを指定
	// 			$cmykProfilePath = $this->cmykIccPath;
	// 			$srgbProfilePath = $this->srgbIccPath;

	// 			// 既存のプロファイルを削除
	// 			$this->image->profileImage('*', null);
	// 			$this->image->profileImage('icc', file_get_contents($cmykProfilePath));
	// 			$this->image->profileImage('icc', file_get_contents($srgbProfilePath));
	// 			$this->image->modulateImage(100, 110, 100); // 明るさ(100%), 彩度(110%), 色相(100%)
	// 			$this->image->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
	// 		}

	// 		$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);

	// 		if($colorSpace==\Imagick::COLORSPACE_CMYK) {
	// 			$canvas->setImageColorspace(Imagick::COLORSPACE_SRGB);
	// 			$canvas->setImageFormat('jpeg');
	// 		}

	// 		// 最終的な画像としてキャンバスを保存
	// 		$this->image = $canvas;
	// 	}catch (Exception $e){
	// 		throw $e;
	// 	}
	// }

	public function save($filePath, $quality = 100) {
		try {
			if($this->isCrop == false){
				copy($this->imagePath, $filePath);
			}else{
				// 设置图像压缩质量
				$this->image->setImageCompressionQuality($quality);

				// 根据文件扩展名保存图像
				$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
				print($extension."\n");
				switch ($extension) {
					case 'jpg':
					case 'jpeg':
						$this->image->setImageFormat('jpeg');
						break;
					case 'png':
						$this->image->setImageFormat('png');
						break;
					case 'gif':
						$this->image->setImageFormat('gif');
						break;
					default:
						return false;
				}
				print($filePath."\n");
				// 保存图像到指定路径
				$this->image->writeImage($filePath);
			}
		}catch (Exception $e){
			throw $e;
		}
	}

	public function clean() {
		// 清理图像资源
		$this->image->clear();
		$this->image->destroy();
	}
}
?>
