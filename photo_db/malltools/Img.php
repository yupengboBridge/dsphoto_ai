<?php

class Img {

	private $image;
	private $imagePath;
	private $isCrop;
	public $cmykIccPath;
	public $adobeRgbIccPath; // Changed from srgbIccPath to adobeRgbIccPath
	
	// Cache ICC profiles to avoid repeated file_get_contents()
	private $cachedCmykProfile = null;
	private $cachedAdobeRgbProfile = null;

	public function __construct()
	{
		if (!extension_loaded('imagick')) {
			throw new CrashException('未インストールの拡張機能：imagick');
		}
		$this->image = new \Imagick();
		$this->isCrop = true;
		
		// Set default ICC profile paths
		$this->setDefaultICCPaths();
		
		// Pre-load ICC profiles for better performance
		$this->loadICCCache();
	}

	/**
	 * Set default ICC profile paths relative to this file location
	 */
	private function setDefaultICCPaths() {
		$baseDir = dirname(__FILE__);
		$this->cmykIccPath = $baseDir . '/icc/JapanColor2011Coated.icc';
		$this->adobeRgbIccPath = $baseDir . '/icc/AdobeRGB1998.icc';
	}

	/**
	 * Pre-load ICC profiles into memory for better performance
	 */
	private function loadICCCache() {
		try {
			// Load Adobe RGB profile (small file - always load)
			if (file_exists($this->adobeRgbIccPath)) {
				$this->cachedAdobeRgbProfile = file_get_contents($this->adobeRgbIccPath);
			}
		} catch (Exception $e) {
			
		}
	}

	/**
	 * Check if image has CMYK ICC profile
	 */
	private function isCMYKImage() {
		try {
			$profiles = $this->image->getImageProfiles('icc');
			if (!empty($profiles)) {
				return $this->image->getImageColorspace() === Imagick::COLORSPACE_CMYK;
			}
			return false;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Apply Adobe RGB profile to all images (CMYK and RGB)
	 */
	private function convertCMYKtoAdobeRGB() {
		$wasCMYK = $this->isCMYKImage();
		
		if (!$wasCMYK) {
			return $this->applyAdobeRGBProfile();
		}
		
		if (!file_exists($this->cmykIccPath)) {
			throw new Exception('CMYK ICC profile not found: ' . $this->cmykIccPath);
		}
		if (!file_exists($this->adobeRgbIccPath)) {
			throw new Exception('Adobe RGB ICC profile not found: ' . $this->adobeRgbIccPath);
		}
		
		try {
			try {
				$this->image->removeImageProfile('icc');
			} catch (Exception $e) {
			}
			
			// Load CMYK profile on-demand (large file - 1.9MB)
			if ($this->cachedCmykProfile === null) {
				$this->cachedCmykProfile = file_get_contents($this->cmykIccPath);
			}
			
			$this->image->profileImage('icc', $this->cachedCmykProfile);
			
			// Apply Adobe RGB profile using cached data
			$this->image->profileImage('icc', $this->cachedAdobeRgbProfile);
			
			// Let Imagick handle colorspace naturally - no forcing
			$this->image->setImageFormat('jpeg');
			
			return true;
		} catch (Exception $e) {
			throw new Exception('CMYK to Adobe RGB conversion failed: ' . $e->getMessage());
		}
	}

	/**
	 * Apply Adobe RGB profile to RGB images
	 */
	private function applyAdobeRGBProfile() {
		if (!file_exists($this->adobeRgbIccPath)) {
			throw new Exception('Adobe RGB ICC profile not found: ' . $this->adobeRgbIccPath);
		}
		
		try {
			$this->image->profileImage('icc', $this->cachedAdobeRgbProfile);
			return true;
		} catch (Exception $e) {
			throw new Exception('Adobe RGB profile application failed: ' . $e->getMessage());
		}
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

			// Always apply Adobe RGB profile to ensure consistent color space
			$needsConversion = $this->convertCMYKtoAdobeRGB();

			if ($originalWidth == $width && $originalHeight == $height) {
				$this->isCrop = $needsConversion; // Set true if conversion was performed
				return;
			}

			$colorSpace = $this->image->getImageColorspace();

			// アスペクト比の判定
			if ($originalWidth > $originalHeight) {
				// 横長画像 → 高さ600にスケーリング後、中央800pxをクロップ（白ベタなし）
				$scale = $height / $originalHeight;
				$scaledWidth = (int)($originalWidth * $scale);
				$this->image->scaleImage(0, $height);

				if ($scaledWidth < $width) {
					// 縦長画像と同じ処理（白ベタ追加）
					$x = max(0, ($width - $scaledWidth) / 2);
					$canvas = new \Imagick();
					$canvas->newImage($width, $height, 'white', 'jpg');

					// 色空間変換（CMYK to Adobe RGB）
					$wasConverted = $this->convertCMYKtoAdobeRGB();

					$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, 0);
					
					// Apply Adobe RGB profile to canvas if conversion was performed
					if ($wasConverted) {
						try {
							$canvas->profileImage('icc', $this->cachedAdobeRgbProfile);
						} catch (Exception $e) {
							// Continue if profile application fails
						}
					}
					
					$canvas->setImageFormat('jpeg');
					$this->image = $canvas;
				}else{
					// 中央800pxを切り抜く
					$xCrop = max(0, ($scaledWidth - $width) / 2);
					$this->image->cropImage($width, $height, $xCrop, 0);
				}
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

				// 色空間変換（CMYK to Adobe RGB）
				$wasConverted = $this->convertCMYKtoAdobeRGB();

				$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, 0);
				
				// Apply Adobe RGB profile to canvas if conversion was performed
				if ($wasConverted) {
					try {
						$canvas->profileImage('icc', $this->cachedAdobeRgbProfile);
					} catch (Exception $e) {
						// Continue if profile application fails
					}
				}
				
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

				// 色空間変換（CMYK to Adobe RGB）
				$wasConverted = $this->convertCMYKtoAdobeRGB();

				$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);
				
				// Apply Adobe RGB profile to canvas if conversion was performed
				if ($wasConverted) {
					try {
						$canvas->profileImage('icc', $this->cachedAdobeRgbProfile);
					} catch (Exception $e) {
						// Continue if profile application fails
					}
				}
				
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

		// Ensure Adobe RGB profile is applied before compositing
		$this->convertCMYKtoAdobeRGB();

		// キャンバスに貼り付け（中央）
		$canvas->compositeImage($this->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);

		// Always apply Adobe RGB profile to the final canvas
		try {
			$canvas->profileImage('icc', $this->cachedAdobeRgbProfile);
		} catch (Exception $e) {
		}

		$this->image = $canvas;
		$this->isCrop = true;
	}

	public function save($filePath, $quality = 100) {
		try {
			if($this->isCrop == false){
				copy($this->imagePath, $filePath);
			}else{
				// 设置图像压缩质量
				$this->image->setImageCompressionQuality($quality);

				// 根据文件扩展名保存图像
				$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
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
				
				// Check if image object is valid
				if (!$this->image || !$this->image->valid()) {
					throw new Exception("Invalid image object before save");
				}
				
				// 保存图像到指定路径
				$this->image->writeImage($filePath);
				
				// Verify file was created
				if (file_exists($filePath)) {
				} else {
					throw new Exception("File was not created: $filePath");
				}
			}
		}catch (Exception $e){
			throw $e;
		}
	}

	public function clean() {
		// 清理图像资源
		if ($this->image === null) {
			return; // Mock mode
		}
		
		$this->image->clear();
		$this->image->destroy();
	}
}
?>
