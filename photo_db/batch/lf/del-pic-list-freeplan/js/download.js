function download() {
    // CSVファイルダウンロード処理
    $.ajax({
        type: "GET",
        async: true,
        cache: false,
        // CSVファイル存在チェック
        url: '../api/filecheck' + '?day=' + $("#datepicker").val(),
        success: function(data, text_status, xhr){
            var res = eval('('+data+')');
            if (res.data === false){
                // CSVファイルが存在しない場合はエラーダイアログ表示
                $("#modal-tgl").trigger('click');
                return;
            }
            // CSVファイルダウンロード
            location.href = '../api/download' + '?day=' + $("#datepicker").val();
        },
        error: function(xhr, text_status, err_thrown){
            alert(' ファイルダウンロードに失敗しました');
            return;
        }
    });
}