<?php
if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "..";
}

require_once ($root_path.'/config.php');
require_once ($root_path.'/lib.php');
require_once ($root_path.'/malltools/mall_image_batch.php');
require_once ($root_path.'/malltools/Config.php');
require_once ($root_path.'/malltools/Img.php');
require_once ($root_path.'/malltools/Log.php');
require_once ($root_path.'/malltools/service/TaskService.php');
require_once ($root_path.'/malltools/service/S3/S3.php');
require_once ($root_path.'/malltools/TaskResult.php');
require_once ($root_path.'/malltools/exception/BaseException.php');
require_once ($root_path.'/malltools/exception/WarningException.php');
require_once ($root_path.'/malltools/exception/CrashException.php');
require_once ($root_path.'/malltools/Mail.php');


class Task
{

    private $config;
    private $taskResult;
    private $s3Config;
    private $image;
    public $cropConfig;
    public $connect;
    public $extension_day;
    public $download_path;
    public $image_path;
    public $csv_path;
    public $processed_path;
    public $crop_save_path;
    public $crop_config_file;
    public $csv_name;
    public $download_image_dir_name;
	public $cmykIccPath;
	public $srgbIccPath;

    //初期化クラス
    public function __construct()
    {
        global $root_path;

        try {
            $this->crop_config_file = $root_path.'/malltools/config/config.ini';
            $this->download_path = $root_path.'/malltools/download/';
            $this->processed_path = $root_path.'/malltools/download/processed/';
            $this->download_image_dir_name = "images";
            $this->image_path = $root_path.'/malltools/download/'.$this->download_image_dir_name.'/';
            $this->csv_name = "mall_dsphoto_list.csv";
            $this->csv_path = $root_path.'/malltools/download/csv/'.$this->csv_name;
            $this->crop_save_path = $root_path.'/malltools/webLimited/';

            $this->config = new Config($this->crop_config_file); //Config
            $this->image = new Img();
			
			$this->cmykIccPath = $root_path.'/malltools/icc/JapanColor2011Coated.icc';
			$this->srgbIccPath = $root_path.'/malltools/icc/sRGB_v4_ICC_preference_displayclass.icc';
			
			$this->image->cmykIccPath = $this->cmykIccPath;
			$this->image->srgbIccPath = $this->srgbIccPath;
			
            $this->taskService = new TaskService();
            $this->taskResult = TaskResult::getInstance();
            $this->taskResult->startTime = time();
            $this->connect = db_connect();
            $this->cropConfig = $this->config->readConfig('crop');
            $this->extension_day = date('Y-m-d H:i:s', strtotime('+3 year'));
        } catch (Exception $e) {
            CommonUtil::writeUploadPhotoImageLog("Task:::__construct:::".$e->getMessage(),$root_path);
            throw new CrashException($e->getMessage());
        }
    }

    //タスクの実行
    public function run($argv){
        global $root_path;

        $data_col_a = [
            "SP25-034500A.jpg",
            "SP25-034501A.jpg",
            "SP25-034502A.jpg",
            "SP25-034503A.jpg",
            "SP25-034504A.jpg",
            "SP25-034505A.jpg",
            "SP25-034506A.jpg",
            "SP25-034507A.jpg",
            "SP25-034508A.jpg",
            "SP25-034509A.jpg",
            "SP25-034510A.jpg",
            "SP25-034511A.jpg",
            "SP25-034512A.jpg",
            "SP25-034513A.jpg",
            "SP25-034514A.jpg",
            "SP25-034515A.jpg",
            "SP25-034516A.jpg",
            "SP25-034517A.jpg",
            "SP25-034518A.jpg",
            "SP25-034519A.jpg",
            "SP25-034520A.jpg",
            "SP25-034521A.jpg",
            "SP25-034522A.jpg",
            "SP25-034597A.jpg",
            "SP25-034598A.jpg",
            "SP25-034599A.jpg",
            "SP25-034600A.jpg",
            "SP25-034601A.jpg",
            "SP25-034602A.jpg",
            "SP25-034603A.jpg",
            "SP25-034604A.jpg",
            "SP25-034611A.jpg",
            "SP25-034616A.jpg",
            "SP25-034621A.jpg",
            "SP25-034622A.jpg",
            "SP25-034623A.jpg",
            "SP25-034624A.jpg",
            "SP25-034625A.jpg",
            "SP25-034626A.jpg",
            "SP25-034633A.jpg",
            "SP25-034634A.jpg",
            "SP25-034635A.jpg",
            "SP25-034636A.jpg",
            "SP25-034639A.jpg",
            "SP25-034640A.jpg",
            "SP25-034641A.jpg",
            "SP25-034642A.jpg",
            "SP25-034643A.jpg",
            "SP25-034644A.jpg",
            "SP25-034645A.jpg",
            "SP25-034646A.jpg",
            "SP25-034647A.jpg",
            "SP25-034648A.jpg",
            "SP25-034651A.jpg",
            "SP25-034652A.jpg",
            "SP25-034653A.jpg",
            "SP25-034654A.jpg",
            "SP25-034655A.jpg",
            "SP25-034656A.jpg",
            "SP25-034657A.jpg",
            "SP25-034658A.jpg",
            "SP25-034659A.jpg",
            "SP25-034660A.jpg",
            "SP25-034661A.jpg",
            "SP25-034662A.jpg",
            "SP25-034663A.jpg",
            "SP25-034664A.jpg",
            "SP25-034665A.jpg",
            "SP25-034667A.jpg",
            "SP25-034674A.jpg",
            "SP25-034677A.jpg",
            "SP25-034678A.jpg",
            "SP25-034679A.jpg",
            "SP25-034680A.jpg",
            "SP25-034681A.jpg",
            "SP25-034682A.jpg",
            "SP25-034683A.jpg",
            "SP25-034684A.jpg",
            "SP25-034685A.jpg",
            "SP25-034686A.jpg",
            "SP25-034687A.jpg",
            "SP25-034688A.jpg",
            "SP25-034689A.jpg",
            "SP25-034690A.jpg",
            "SP25-034691A.jpg",
            "SP25-034695A.jpg",
            "SP25-034696A.jpg",
            "SP25-034697A.jpg",
            "SP25-034698A.jpg",
            "SP25-034700A.jpg",
            "SP25-034701A.jpg",
            "SP25-034702A.jpg",
            "SP25-034703A.jpg",
            "SP25-034704A.jpg",
            "SP25-034705A.jpg",
            "SP25-034706A.jpg",
            "SP25-034707A.jpg",
            "SP25-034708A.jpg",
            "SP25-034709A.jpg",
            "SP25-034711A.jpg",
            "SP25-034712A.jpg",
            "SP25-034713A.jpg",
            "SP25-034714A.jpg",
            "SP25-034715A.jpg",
            "SP25-034716A.jpg",
            "SP25-034717A.jpg",
            "SP25-034718A.jpg",
            "SP25-034719A.jpg",
            "SP25-034720A.jpg",
            "SP25-034721A.jpg",
            "SP25-034722A.jpg",
            "SP25-034723A.jpg",
            "SP25-034724A.jpg",
            "SP25-034725A.jpg",
            "SP25-034726A.jpg",
            "SP25-034727A.jpg",
            "SP25-034728A.jpg",
            "SP25-034729A.jpg",
            "SP25-034730A.jpg",
            "SP25-034737A.jpg",
            "SP25-034738A.jpg",
            "SP25-034739A.jpg",
            "SP25-034740A.jpg",
            "SP25-034741A.jpg",
            "SP25-034742A.jpg",
            "SP25-034743A.jpg",
            "SP25-034744A.jpg",
            "SP25-034745A.jpg",
            "SP25-034746A.jpg",
            "SP25-034747A.jpg",
            "SP25-034748A.jpg",
            "SP25-034749A.jpg",
            "SP25-034750A.jpg",
            "SP25-034751A.jpg",
            "SP25-034752A.jpg",
            "SP25-034753A.jpg",
            "SP25-034755A.jpg",
            "SP25-034756A.jpg",
            "SP25-034757A.jpg",
            "SP25-034758A.jpg",
            "SP25-034759A.jpg",
            "SP25-034760A.jpg",
            "SP25-034761A.jpg",
            "SP25-034765A.jpg",
            "SP25-034766A.jpg",
            "SP25-034767A.jpg",
            "SP25-034768A.jpg",
            "SP25-034769A.jpg",
            "SP25-034770A.jpg",
            "SP25-034771A.jpg",
            "SP25-034772A.jpg",
            "SP25-034773A.jpg",
            "SP25-034774A.jpg",
            "SP25-034775A.jpg",
            "SP25-034776A.jpg",
            "SP25-034777A.jpg",
            "SP25-034778A.jpg",
            "SP25-034779A.jpg",
            "SP25-034780A.jpg",
            "SP25-034781A.jpg",
            "SP25-034785A.jpg",
            "SP25-034786A.jpg",
            "SP25-034787A.jpg",
            "SP25-034788A.jpg",
            "SP25-034789A.jpg",
            "SP25-034790A.jpg",
            "SP25-034791A.jpg",
            "SP25-034792A.jpg",
            "SP25-034793A.jpg",
            "SP25-034797A.jpg",
            "SP25-034798A.jpg",
            "SP25-034799A.jpg",
            "SP25-034800A.jpg",
            "SP25-034801A.jpg",
            "SP25-034802A.jpg",
            "SP25-034803A.jpg",
            "SP25-034804A.jpg",
            "SP25-034806A.jpg",
            "SP25-034826A.jpg",
            "SP25-034827A.jpg",
            "SP25-034828A.jpg",
            "SP25-034829A.jpg",
            "SP25-034830A.jpg",
            "SP25-034831A.jpg",
            "SP25-034834A.jpg",
            "SP25-034855A.jpg",
            "SP25-034856A.jpg",
            "SP25-034857A.jpg",
            "SP25-034867A.jpg",
            "SP25-034868A.jpg",
            "SP25-034869A.jpg",
            "SP25-034870A.jpg",
            "SP25-034871A.jpg",
            "SP25-034872A.jpg",
            "SP25-034873A.jpg",
            "SP25-034874A.jpg",
            "SP25-034875A.jpg",
            "SP25-034876A.jpg",
            "SP25-034878A.jpg",
            "SP25-034879A.jpg",
            "SP25-034880A.jpg",
            "SP25-034881A.jpg",
            "SP25-034882A.jpg",
            "SP25-034883A.jpg",
            "SP25-034884A.jpg",
            "SP25-034885A.jpg",
            "SP25-034886A.jpg",
            "SP25-034887A.jpg",
            "SP25-034888A.jpg",
            "SP25-034889A.jpg",
            "SP25-034890A.jpg",
            "SP25-034891A.jpg",
            "SP25-034895A.jpg",
            "SP25-034896A.jpg",
            "SP25-034899A.jpg",
            "SP25-034900A.jpg",
            "SP25-034901A.jpg",
            "SP25-034902A.jpg",
            "SP25-034903A.jpg",
            "SP25-034904A.jpg",
            "SP25-034905A.jpg",
            "SP25-034906A.jpg",
            "SP25-034907A.jpg",
            "SP25-034908A.jpg",
            "SP25-034909A.jpg",
            "SP25-034910A.jpg",
            "SP25-034911A.jpg",
            "SP25-034912A.jpg",
            "SP25-034913A.jpg",
            "SP25-034914A.jpg",
            "SP25-034915A.jpg",
            "SP25-034916A.jpg",
            "SP25-034918A.jpg",
            "SP25-034919A.jpg",
            "SP25-034920A.jpg",
            "SP25-034921A.jpg",
            "SP25-034922A.jpg",
            "SP25-034923A.jpg",
            "SP25-034924A.jpg",
            "SP25-034925A.jpg",
            "SP25-034926A.jpg",
            "SP25-034927A.jpg",
            "SP25-034928A.jpg",
            "SP25-034929A.jpg",
            "SP25-034930A.jpg",
            "SP25-034931A.jpg",
            "SP25-034932A.jpg",
            "SP25-034933A.jpg",
            "SP25-034934A.jpg",
            "SP25-034935A.jpg",
            "SP25-034936A.jpg",
            "SP25-034937A.jpg",
            "SP25-034938A.jpg",
            "SP25-034939A.jpg",
            "SP25-034940A.jpg",
            "SP25-034941A.jpg",
            "SP25-034957A.jpg",
            "SP25-034958A.jpg",
            "SP25-034959A.jpg",
            "SP25-034960A.jpg",
            "SP25-034961A.jpg",
            "SP25-034962A.jpg",
            "SP25-034963A.jpg",
            "SP25-034964A.jpg",
            "SP25-034965A.jpg",
            "SP25-034966A.jpg",
            "SP25-034967A.jpg",
            "SP25-034968A.jpg",
            "SP25-034969A.jpg",
            "SP25-034970A.jpg",
            "SP25-034971A.jpg",
            "SP25-034972A.jpg",
            "SP25-034974A.jpg",
            "SP25-034975A.jpg",
            "SP25-034976A.jpg",
            "SP25-034977A.jpg",
            "SP25-034978A.jpg",
            "SP25-034979A.jpg",
            "SP25-034982A.jpg",
            "SP25-034983A.jpg",
            "SP25-034984A.jpg",
            "SP25-034985A.jpg",
            "SP25-034988A.jpg",
            "SP25-034989A.jpg",
            "SP25-034990A.jpg",
            "SP25-034991A.jpg",
            "SP25-034992A.jpg",
            "SP25-034994A.jpg",
            "SP25-034995A.jpg",
            "SP25-035001A.jpg",
            "SP25-035002A.jpg",
            "SP25-035004A.jpg",
            "SP25-035007A.jpg",
            "SP25-035008A.jpg",
            "SP25-035009A.jpg",
            "SP25-035011A.jpg",
            "SP25-035012A.jpg",
            "SP25-035013A.jpg",
            "SP25-035014A.jpg",
            "SP25-035015A.jpg",
            "SP25-035016A.jpg",
            "SP25-035017A.jpg",
            "SP25-035018A.jpg",
            "SP25-035019A.jpg",
            "SP25-035021A.jpg",
            "SP25-035022A.jpg",
            "SP25-035023A.jpg",
            "SP25-035024A.jpg",
            "SP25-035027A.jpg",
            "SP25-035028A.jpg",
            "SP25-035029A.jpg",
            "SP25-035030A.jpg",
            "SP25-035034A.jpg",
            "SP25-035035A.jpg",
            "SP25-035038A.jpg",
            "SP25-035039A.jpg",
            "SP25-035041A.jpg",
            "SP25-035042A.jpg",
            "SP25-035043A.jpg",
            "SP25-035044A.jpg",
            "SP25-035045A.jpg",
            "SP25-035047A.jpg",
            "SP25-035051A.jpg",
            "SP25-035052A.jpg",
            "SP25-035055A.jpg",
            "SP25-035056A.jpg",
            "SP25-035057A.jpg",
            "SP25-035058A.jpg",
            "SP25-035059A.jpg",
            "SP25-035060A.jpg",
            "SP25-035061A.jpg",
            "SP25-035062A.jpg",
            "SP25-035063A.jpg",
            "SP25-035064A.jpg",
            "SP25-035065A.jpg",
            "SP25-035066A.jpg",
            "SP25-035067A.jpg",
            "SP25-035072A.jpg",
            "SP25-035073A.jpg",
            "SP25-035074A.jpg",
            "SP25-035075A.jpg",
            "SP25-035076A.jpg",
            "SP25-035077A.jpg",
            "SP25-035078A.jpg",
            "SP25-035079A.jpg",
            "SP25-035080A.jpg",
            "SP25-035081A.jpg",
            "SP25-035082A.jpg",
            "SP25-035083A.jpg",
            "SP25-035086A.jpg",
            "SP25-035087A.jpg",
            "SP25-035088A.jpg",
            "SP25-035089A.jpg",
            "SP25-035090A.jpg",
            "SP25-035092A.jpg",
            "SP25-035093A.jpg",
            "SP25-035094A.jpg",
            "SP25-035095A.jpg",
            "SP25-035096A.jpg",
            "SP25-035097A.jpg",
            "SP25-035098A.jpg",
            "SP25-035099A.jpg",
            "SP25-035100A.jpg"
        ];

        for($i=0;$i<count($data_col_a);$i++){
            try {
                $this->process_update($data_col_a[$i]);
            } catch (Exception $e) {
                $this->taskResult->isError = true;
                $this->taskResult->setErrorMsg($e->getMessage());
                CommonUtil::writeUploadPhotoImageLog(
                    "MALL番号【{$data_col_a[$i]}】にはエラーが発生しました、エラーの原因:" . $e->getMessage(),
                    $root_path
                );
            }
        }

        $this->taskResult->endTime = time();
        return $this->taskResult;
    }

    /**
     * @throws Exception
     */
    private function process_update($data_col_a){
        global $root_path;

        try {
            $mall_no = funcGetMallNo($data_col_a);
            $photo = CommonPhotoImage::getPhotoByMallNo($this->connect,$mall_no);
            if(is_null($photo)){
                $error_message = "MALL番号:".$mall_no.":::見つかりませんでした.:::";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
            }else{
                $this->image = new Img();
                
                $this->image->cmykIccPath = $this->cmykIccPath;
                $this->image->srgbIccPath = $this->srgbIccPath;
            
                $this->image->load($this->image_path.$data_col_a);
                $prefix = strtoupper(substr($data_col_a, 0, 2));
                if ($prefix === 'LF' || $prefix === 'LH') {
                    $this->image->cropForLHAndLF($this->cropConfig['width'], $this->cropConfig['height']);
                } else {
                    $this->image->crop($this->cropConfig['width'], $this->cropConfig['height']);
                }
                $outputFile = $this->crop_save_path.$data_col_a;
                $this->image->save($outputFile);
                $this->image->clean();

                updatePhotoImageThumbAll($data_col_a);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
?>
