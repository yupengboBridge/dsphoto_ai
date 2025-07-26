<?php
require_once('./config.php');
require_once('./lib.php');

if(!session_id()) session_start();
$s_login_id = array_get_value($_SESSION,'login_id' ,"");
if(empty($s_login_id)){
    header_out($logout_page);
}
$imageList = [];
$csvList = [];

if (@$_POST['submit']) {
    global $imageList;
    global $csvList;
    $imageList = [];
    $csvList = [];

    $path_csv = './webCsv';
    $path_image = './webLimited';
    $extList_csv = ['csv'];
    $extList_image = ['jpg', 'jpeg'];
    if (!is_dir($path_csv)) {
        mkdir($path_csv, 0777, true);
    }
    if (!is_dir($path_image)) {
        mkdir($path_image, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['file_csv']['name'], PATHINFO_EXTENSION));
    $fileName = $path_csv . '/' . $_FILES['file_csv']['name'];
    // アップロードCSV
    if (!move_uploaded_file($_FILES['file_csv']['tmp_name'], $fileName)) {
        echo 'CSVファイルのアップロードはエラーになりました。';
        return;
    }
    $csvFile = $fileName;

    // 複数画像ファイルをアップロード
    for ($i = 0; $i < count($_FILES["file_image"]['name']); $i++) {
        $ext = strtolower(pathinfo($_FILES['file_image']['name'][$i], PATHINFO_EXTENSION));
        $fileName = $path_image . '/' . $_FILES['file_image']['name'][$i];
        if (!move_uploaded_file($_FILES['file_image']['tmp_name'][$i], $fileName)) {
            echo 'ファイルのアップロードはエラーになりました。' . '</br>';
            return;
        }
        array_push($imageList, $_FILES['file_image']['name'][$i]);
    }
    // CSVファイルを取り込み、CSVファイルが存在する場合、CSVファイルからアレイに書き込む
    $fp = fopen($csvFile, 'r');
    $start = 0;


    while ($line = fgetcsv($fp, 0, "\t")) {
        if ($start == 0) {
            $start += 1;
        } else {
            if (in_array($line[0], $imageList)) {
                array_push($csvList, $line);
            }
        }
    }
    fclose($fp);
}
?>
<style>
    .label_item{
        display: inline-block;
        width: 130px;
    }
    .div_buttons{
        text-align: center;
        padding-top: 40%;
        padding-left: 404px;
    }
    .div_main{
        float: left;
        width: 68%;
        border: 1px solid;
        padding-left: 15px;
        padding-top: 7px;
        margin-bottom: 8px;
        pointer-events: none;
    }
    .input_w{
        width:77%;
    }
    .input_w60{
        width: 60%;
    }
    .input_button{
        width: 300px;
        font-size: 30px;
        background: cornflowerblue;
        border: 1px solid;
        border-radius: 7px;
    }
    .div_label{
        display: inline-block;
        width: 170px;
    }
    .w400{
        width: 400px;
    }
    .form_css{
        border: 1px solid #000;
        padding-left: 11px;
        width: 430px;
        padding-bottom: 5px;
        padding-top: 5px;
    }
    .div_left{
        float: left;
        width: 30%;
    }
</style>
<div class="div_left">
    <form method='post' action='web_uploads.php' enctype='multipart/form-data' class="form_css"	>
        <label for="upload_csv">
            <div class="div_label"><div>CSVファイル</div></div>
            <input type="file" name="file_csv"  id="upload_csv">
        </label>
        <br/>
        <label for="Upload_image">
            <div class="div_label"><div>イメージファイルパス</div></div>
            <input type="file" name="file_image[]" id="Upload_image" multiple webkitdirectory>
        </label>
        <br/>
        <input type='submit' name='submit' value='アップロード'>
    </form>
    <hr/>
    <select id='csvLine' class='w400' multiple='true' size='10' onkeyup="handleKeyUp(event)" onkeydown="handleKeyDown(event)">
        <?php
        global $csvList;
        if(count($csvList) == 0){
            echo "<option value=''></option>";
        }else{
            foreach ($csvList as $value) {
                $str = json_encode($value);
                echo "<option value='".$str."'>";
                echo $value[0];
                echo "</option>";
            }
        }
        ?>
    </select>
    <br/><br/>
    <img id='imageUrl' src='' height='200px'>
    <br/><br/>
    <select id='errorData' class='w400' size='5'></select>
    <p id="return"></p>
</div>

<div class="div_main">
    <label for="upload_csv">
        <div class="label_item"><div>画像ファイル名</div></div>
        <input id="1" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>画像番号</div></div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>掲載状況</div></div>
        <input value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>登録区分</div></div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>元画像管理番号</div></div>
        <input id="2" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>BUD_PHOTO番号</div></div>
        <input id="3" value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>写真名</div></div>
        <input id="4" value="" class="input_w">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>分類</div></div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>方面</div></div>
        <input id="5" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>国・都道府県</div></div>
        <input id="6" value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>地名</div></div>
        <input id="7" value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>内容</div></div>
        <input id="8" value="" class="input_w">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>撮影時期</div></div>
        <input id="9" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>登録日</div></div>
        <input id="10" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>終了日</div></div>
        <input id="11" value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>写真入手元</div></div>
        <input id="12" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>使用範囲</div></div>
        <input id="13" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>版権所有者</div></div>
        <input value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>出稿条件</div></div>
        <input value="" class="input_w">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>付加条件</div></div>
        <input value="" class="input_w">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>クレジット</div></div>
        <input id="14" value="" class="input_w60">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>独占使用</div></div>
        <input type="checkbox" value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>カテゴリー</div></div>
        <input id="15" value="" class="input_w">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>担当部署</div></div>
        <input id="16" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>担当氏名</div></div>
        <input id="17" value="">
    </label>
    <label for="upload_csv">
        <div class="label_item"><div>申請アカウント</div></div>
        <input value="">
    </label>
    <br/><br/>
    <label for="upload_csv">
        <div class="label_item"><div>コメント</div></div>
        <input id="18" value="" class="input_w">
    </label>
    <br/><br/>
</div>
<div class="div_buttons">
    <input id="button_all" type="button" onclick="b_all()" value="全部アップロード" class="input_button">
    <input id="button_check" type="button" onclick="b_check()" value="アップロード（選択）" class="input_button">
    <input id="button_delete" type="button" onclick="b_delete()" value="レコード削除" class="input_button">
</div>
<script src="./js/jquery.min.js"></script>
<script>
    const select = document.getElementById("csvLine");
    const imgurl = document.getElementById("imageUrl");
    const button_all = document.getElementById("button_all");
    const button_check = document.getElementById("button_check");
    const button_delete = document.getElementById("button_delete");
    const summarize = document.getElementById("return");
    function b_all(){
        summarize.innerHTML = "";
        clearError()
        var str = [];
        for(let i=0;i<select.options.length;i++){
            if(!select.options[i].value){
                return false;
            }
            var dataList = JSON.parse(select.options[i].value)
            const bud_photo_no = dataList[0].replace('-', "_-")
            let date = ""
            if(dataList[9] != undefined && dataList[9] != ""){
                date = dataList[9].slice(0,10)
            }
            /*
            * 0:画像管理番号			=> ""
            * 4:元画像管理番号			=> レンポジ番号(W列) dataList[22]
            * 5:BUD_PHOTO番号			=> ファイル名(A列)	dataList[0]
            * 6:写真名					=> 内容(U列)	dataList[20]
            * 7:分類ID					=> ""
            * 8:方面ID					=> 国内海外(N列)	dataList[13]
            * 9:都道府県				=> 国名県名(O列)	dataList[14]
            * 10:地名ID					=> 地名(P列)	dataList[15]
            * 11:内容					=> 内容(U列)	dataList[20]
            * 12:撮影時期				=> 撮影月(Q列)	dataList[16]
            * 14:掲載期間（To）			=> 使用可能終了日(J列)	dataList[9]
            * 15:写真入手元				=> 写真入手元(V列)	dataList[21]
            * 16:使用範囲				=> 他の制作会社への貸出が(AF列)	dataList[31]
            * 18:付加条件：クレジット	=> 要クレジット(Y列)	dataList[24]
            * 19:付加条件：要確認		=> ""
            * 20:独占使用				=> ""
            * 21:カテゴリ				=> カテゴリ(C列)	dataList[2]
            * 22:版権所有者				=> ""
            * 24:お客様部署				=> 担当部署(AA列)	dataList[26]
            * 25:お客様名				=> 担当氏名(AA列)	dataList[27]
            * 31:備考					=> その他のコメント(AL列)	dataList[37]
            * 32:申請者管理番号			=> ""
            */
            var csvcontent =
                dataList[0] + "\t" +
                "" + "\t" +
                "" + "\t" +
                "" + "\t" +
                dataList[22] + "\t" +
                dataList[0] + "\t" +
                dataList[20] + "\t" +
                "" + "\t" +
                dataList[13] + "\t" +
                dataList[14] + "\t" +
                dataList[15] + "\t" +
                dataList[20] + "\t" +
                dataList[16] + "\t" +
                "" + "\t" +
                dataList[9] + "\t" +
                dataList[21] + "\t" +
                dataList[31] + "\t" +
                "" + "\t" +
                dataList[24] + "\t" +
                "" + "\t" +
                "" + "\t" +
                dataList[2] + "\t" +
                "" + "\t" +
                "" + "\t" +
                dataList[26] + "\t" +
                dataList[27] + "\t" +
                "" + "\t" +
                "" + "\t" +
                "" + "\t" +
                "" + "\t" +
                "" + "\t" +
                dataList[37] + "\t" +
                ""
            str.push(csvcontent);
        }
        admin = "admin;BUD管理者"
        $.ajax({
            type: 'POST',
            url: 'web_among_uploads.php',
            async:false,
            dataType: "json",
            data: ({csvcontentList:str,s_logininfo:admin}),
            success: function(response){
                alert('アップロード成功しました');
                var res = response
                showError(res)
                var success = res[0]
                var error = res[1]
                var repeat = res[2]
                const s_l = success.length
                const e_l = error.length
                const r_l = repeat.length
                const sum = s_l + e_l + r_l;
                summarize.innerHTML = '総計：' + sum + '；成功：' + s_l + '；失敗：' + e_l + '；重複：' + r_l;
            },
            error: function (response){
                alert('error:' + 'アップロードが失敗しました。');
            }
        });
    }
    function b_check(){
        summarize.innerHTML = "";
        clearError()
        var str = [];
        for(let i=0;i<select.length;i++){
            if (select.options[i].selected){
                var dataList = JSON.parse(select.options[i].value)
                const bud_photo_no = dataList[0].replace('-', "_-")
                let date = ""
                if(dataList[9] != undefined && dataList[9] != ""){
                    date = dataList[9].slice(0,10)
                }
                var csvcontent =
                    dataList[0] + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    dataList[22] + "\t" +
                    dataList[0] + "\t" +
                    dataList[20] + "\t" +
                    "" + "\t" +
                    dataList[13] + "\t" +
                    dataList[14] + "\t" +
                    dataList[15] + "\t" +
                    dataList[20] + "\t" +
                    dataList[16] + "\t" +
                    "" + "\t" +
                    dataList[9] + "\t" +
                    dataList[21] + "\t" +
                    dataList[31] + "\t" +
                    "" + "\t" +
                    dataList[24] + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    dataList[2] + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    dataList[26] + "\t" +
                    dataList[27] + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    "" + "\t" +
                    dataList[37] + "\t" +
                    ""
                str.push(csvcontent);
                // str.push(csvcontent);
            }
        }
        admin = "admin;BUD管理者"
        $.ajax({
            type: 'POST',
            url: 'web_among_uploads.php',
            async:false,
            dataType: "json",
            data: ({csvcontentList:str,s_logininfo:admin}),
            success: function(response){
                alert('アップロード成功しました');
                var res = response
                showError(res)
                var success = res[0]
                var error = res[1]
                var repeat = res[2]
                const s_l = success.length
                const e_l = error.length
                const r_l = repeat.length
                const sum = s_l + e_l + r_l;
                summarize.innerHTML = '総計：' + sum + '；成功：' + s_l + '；失敗：' + e_l + '；重複：' + r_l;
            },
            error: function (response){
                alert('error:' + 'アップロードが失敗しました。');
            }
        });
    }
    function b_delete(){
        var str = [];
        for(let i=select.length-1;i>=0;i--){
            if (select.options[i].selected){
                select.options[i].remove(0);
            }
        }
    }
    function showError(res){
        var success = res[0]
        var error = res[1]
        var repeat = res[2]
        for(let i=0;i<error.length;i++){
            errorData.innerHTML += '<option>失敗：'+error[i]+'</option>';
        }
        for(let i=0;i<repeat.length;i++){
            errorData.innerHTML += '<option>重複：'+repeat[i]+'</option>';
        }
    }
    function clearError(){
        for(let i=errorData.length-1;i>=0;i--){
            errorData.options[i].remove(0);
        }
    }

    function csvLineChange(){
        //let path = "../cms_photo_image/webLimited/" + select.options[select.selectedIndex].text
        var path = "./webLimited/" + select.options[select.selectedIndex].text
        imgurl.src = path;
        var csvList = <?php
            $str = json_encode($csvList);
            echo $str;
            ?>;
        for (var i = 0; i < csvList.length; i++) {
            if (csvList[i][0] == select.options[select.selectedIndex].text) {
                document.getElementById("1").value = csvList[i][0];//画像ファイル名(A列)
                document.getElementById("3").value = csvList[i][0];//BUD_PHOTO番号(A列)
                document.getElementById("2").value = csvList[i][22];//元画像管理番号(W列)
                document.getElementById("4").value = csvList[i][20];//写真名(U列)
                document.getElementById("5").value = csvList[i][13];//方面(N列)
                document.getElementById("6").value = csvList[i][14];//国・都道府県(O列)
                document.getElementById("7").value = csvList[i][15];//地名(P列)
                document.getElementById("8").value = csvList[i][20];//内容(U列)
                document.getElementById("9").value = csvList[i][16];//撮影時期(Q列)
                document.getElementById("10").value = csvList[i][8];//登録日(I列)
                document.getElementById("11").value = csvList[i][9];//終了日(J列)
                document.getElementById("12").value = csvList[i][21];//写真入手元(V列)
                document.getElementById("13").value = csvList[i][31];//使用範囲(AF列)
                document.getElementById("14").value = csvList[i][24];//クレジット(Y列)
                document.getElementById("15").value = csvList[i][2];//カテゴリー(C列)
                document.getElementById("16").value = csvList[i][26];//担当部署(AA列)
                document.getElementById("17").value = csvList[i][27];//担当氏名(AB列)
                document.getElementById("18").value = csvList[i][37];//コメント(AL列)
            }
        }
    }

    var keyDown_SelectedIndex;

    select.addEventListener("change", csvLineChange);

    function handleKeyDown(event) {
        var select = document.getElementById('csvLine');
        var selectedIndex = select.selectedIndex;
        keyDown_SelectedIndex = selectedIndex;
    }

    function handleKeyUp(event){
        var select = document.getElementById('csvLine');
        var selectedIndex = select.selectedIndex;
        var optionsCount = select.options.length;
        if (event.key === 'ArrowUp') {
            if (selectedIndex === 0 && keyDown_SelectedIndex==0) {
                select.selectedIndex = optionsCount - 1;
                csvLineChange();
            }
        } else if (event.key === 'ArrowDown') {
            if (selectedIndex === optionsCount - 1 && keyDown_SelectedIndex==optionsCount - 1) {
                select.selectedIndex = 0;
                csvLineChange();
            }
        }
    }
</script>
