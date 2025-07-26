$(function () {
    // 方面プルダウン生成処理
    $.ajax({
        type: "GET",
        async: true,
        cache: false,
        url: './api/destsel' + '?hei=' + constants.hei + '&type=' + constants.type,
        success: function(data, text_status, xhr){
            var res = eval('('+data+')');
            if (res.data === false){
                alert('方面プルダウン生成に失敗しました');
                return;
            }
            var obj = $.parseJSON(data);
            $.each(obj.data.destination_code, function(key, value) {
                $("#destSel").append("<option value=" + key + ">" + value +  "</option>");
            });
        },
        error: function(xhr, text_status, err_thrown){
            alert('方面プルダウン生成に失敗しました');
            return;
        }
    });
});