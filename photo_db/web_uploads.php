<?php
require_once('./config.php');
require_once('./lib.php');

session_start();
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
<div style="float: left;width: 30%">
    <form method='post' action='web_uploads.php' enctype='multipart/form-data' style="
    border: 1px solid #000;
    padding-left: 11px;
    width: 430px;
    padding-bottom: 5px;
    padding-top: 5px;
"	>
        <label for="upload_csv">
            <div style="display: inline-block;width: 170px">
                <div>CSVファイル</div>
            </div>
            <input type="file" name="file_csv"  id="upload_csv">
        </label>
        <br/>
        <label for="Upload_image">
            <div style="display: inline-block;width: 170px">
                <div>イメージファイルパス</div>
            </div>
            <input type="file" name="file_image[]" id="Upload_image" multiple webkitdirectory>
        </label>
        <br/>
        <input type='submit' name='submit' value='アップロード'>
    </form>
    <hr/>
    <select id='csvLine' style='width: 400px' multiple='true' size='10' onkeyup="handleKeyUp(event)" onkeydown="handleKeyDown(event)">
        <?php
        global $csvList;
        foreach ($csvList as $value) {
            $str = json_encode($value);
            echo "<option value='".$str."'>";
            echo $value[0];
            echo "</option>";
        }
        ?>
    </select>
    <br/>
    <br/>
    <img id='imageUrl' src='' height='200px'>
    <br/>
    <br/>
    <select id='errorData' style='width: 400px;' size='5'>
    </select>
    <p id="return"></p>
</div>

<div style="float: left;width: 68%;border: 1px solid;padding-left: 15px;padding-top: 7px;margin-bottom: 8px;pointer-events: none;">
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>画像ファイル名</div>
        </div>
        <input id="1" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>画像番号</div>
        </div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>掲載状況</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>登録区分</div>
        </div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>元画像管理番号</div>
        </div>
        <input id="2" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>BUD_PHOTO番号</div>
        </div>
        <input id="3" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>写真名</div>
        </div>
        <input id="4" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>分類</div>
        </div>
        <input id="5" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>方面</div>
        </div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>国・都道府県</div>
        </div>
        <input id="6" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>地名</div>
        </div>
        <input id="7" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>内容</div>
        </div>
        <input id="8" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>撮影時期</div>
        </div>
        <input id="9" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>登録日</div>
        </div>
        <input value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>終了日</div>
        </div>
        <input id="10" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>写真入手元</div>
        </div>
        <input id="11" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>使用範囲</div>
        </div>
        <input id="12" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>版権所有者</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>出稿条件</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>付加条件</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>クレジット</div>
        </div>
        <input id="13" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>独占使用</div>
        </div>
        <input type="checkbox" value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>カテゴリー</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>担当部署</div>
        </div>
        <input id="14" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>担当氏名</div>
        </div>
        <input id="15" value="">
    </label>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>申請アカウント</div>
        </div>
        <input value="">
    </label>
    <br/>
    <br/>
    <label for="upload_csv">
        <div style="display: inline-block;width: 130px">
            <div>コメント</div>
        </div>
        <input id="16" value="">
    </label>
    <br/>
    <br/>
    <!--    <div style="text-align: center">-->
    <!--        <input id="button_all" type="button" onclick="b_all()" value="全部アップロード" />-->
    <!--        <input id="button_check" type="button" onclick="b_check()" value="アップロード（選択）" />-->
    <!--        <input id="button_delete" type="button" onclick="b_delete()" value="レコード削除" />-->
    <!--    </div>-->
</div>
<div style="text-align: center;padding-top: 40%;padding-left: 404px;">
    <input id="button_all" type="button" onclick="b_all()" value="全部アップロード" style="
    width: 300px;
    font-size: 30px;
    background: cornflowerblue;
    border: 1px solid;
    border-radius: 7px;
">
    <input id="button_check" type="button" onclick="b_check()" value="アップロード（選択）" style="
    width: 300px;
    font-size: 30px;
    background: cornflowerblue;
    border: 1px solid;
    border-radius: 7px;
">
    <input id="button_delete" type="button" onclick="b_delete()" value="レコード削除" style="
    width: 300px;
    font-size: 30px;
    background: cornflowerblue;
    border: 1px solid;
    border-radius: 7px;
">
</div>
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
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
        for(let i=0;i<select.length;i++){
            var dataList = JSON.parse(select.options[i].value)
            const bud_photo_no = dataList[0].replace('-', "_-")
            let date = ""
            if(dataList[9] != undefined && dataList[9] != ""){
                date = dataList[9].slice(0,10)
            }
            var csvcontent = bud_photo_no + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + dataList[22] + "\t" + dataList[0] + "\t" + dataList[20] + "\t" + dataList[13] + "\t"
                + "" + "\t" + dataList[14] + "\t" + dataList[15] + "\t" + dataList[20] + "\t" + dataList[16] + "\t" + "" + "\t" + date + "\t"
                + dataList[21] + "\t" + dataList[31] + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + dataList[24] + "\t" + "" + "\t" + "" + "\t" + dataList[26] + "\t" + dataList[27] + "\t" + "" + "\t"
                + dataList[37]
            str.push(csvcontent);
        }
        admin = "admin;BUD管理者"
        $.ajax({
            type: 'POST',
            url: 'web_among_uploads.php',
            async:false,
            dataType: "text",
            data: ({csvcontentList:str,s_logininfo:admin}),
            success: function(response){
                alert('アップロード成功しました');
                var res = JSON.parse(response)
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
                var csvcontent = bud_photo_no + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + dataList[22] + "\t" + dataList[0] + "\t" + dataList[20] + "\t" + dataList[13] + "\t"
                    + "" + "\t" + dataList[14] + "\t" + dataList[15] + "\t" + dataList[20] + "\t" + dataList[16] + "\t" + "" + "\t" + date + "\t"
                    + dataList[21] + "\t" + dataList[31] + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + dataList[24] + "\t" + "" + "\t" + "" + "\t" + dataList[26] + "\t" + dataList[27] + "\t" + "" + "\t"
                    + dataList[37]
                str.push(csvcontent);
            }
        }
        admin = "admin;BUD管理者"
        $.ajax({
            type: 'POST',
            url: 'web_among_uploads.php',
            async:false,
            dataType: "text",
            data: ({csvcontentList:str,s_logininfo:admin}),
            success: function(response){
                alert('アップロード成功しました');
                var res = JSON.parse(response)
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
        //let path = "../photo_db/webLimited/" + select.options[select.selectedIndex].text
        let path = "./webLimited/" + select.options[select.selectedIndex].text
        imgurl.src = path;
        var csvList = <?php
            $str = json_encode($csvList);
            echo $str;
            ?>;
        for (var i = 0; i < csvList.length; i++) {
            if (csvList[i][0] == select.options[select.selectedIndex].text) {
                document.getElementById("1").value = csvList[i][0];
                document.getElementById("2").value = csvList[i][22];
                document.getElementById("3").value = csvList[i][0];
                document.getElementById("4").value = csvList[i][20];
                document.getElementById("5").value = csvList[i][13];
                document.getElementById("6").value = csvList[i][14];
                document.getElementById("7").value = csvList[i][15];
                document.getElementById("8").value = csvList[i][20];
                document.getElementById("9").value = csvList[i][16];
                document.getElementById("10").value = csvList[i][9];
                document.getElementById("11").value = csvList[i][21];
                document.getElementById("12").value = csvList[i][31];
                document.getElementById("13").value = csvList[i][24];
                document.getElementById("14").value = csvList[i][26];
                document.getElementById("15").value = csvList[i][27];
                document.getElementById("16").value = csvList[i][37];
                // const bud_photo_no = csvList[i][0].replace('-', "_-")
                // let date = ""
                // if(csvList[i][9] != undefined && csvList[i][9] != ""){
                //     date = csvList[i][9].slice(0,10)
                // }
                // var csvcontent = bud_photo_no + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + csvList[i][22] + "\t" + csvList[i][0] + "\t" + csvList[i][20] + "\t" + csvList[i][13] + "\t"
                //     + "" + "\t" + csvList[i][14] + "\t" + csvList[i][15] + "\t" + csvList[i][20] + "\t" + csvList[i][16] + "\t" + "" + "\t" + date + "\t"
                //     + csvList[i][21] + "\t" + csvList[i][31] + "\t" + "" + "\t" + "" + "\t" + "" + "\t" + csvList[i][24] + "\t" + "" + "\t" + "" + "\t" + csvList[i][26] + "\t" + csvList[i][27] + "\t" + "" + "\t"
                //     + csvList[i][37]
                // admin = "admin;BUD管理者"
                // $.ajax({
                //     type: 'GET',
                //     url: 'web_among_uploads.php',
                //     data: ({csvcontent:csvcontent,s_logininfo:admin}),
                //     success: function(response){
                //         alert(response);
                //     }
                // });
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
