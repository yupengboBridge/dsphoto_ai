$(function () {
    // datepicker処理
    var dateFormat   = 'yyyymmdd';
    var myDate = new Date();
    // 現行3週間分の日にちのみ参照可能。保存する期間が変更になった場合、変更すること
    myDate.setDate (myDate.getDate() - 21);
    var date = new Date();
    date.setDate(date.getDate() - 1);

    $('#datepicker').datepicker({
        format      : dateFormat,
        language    : 'ja',
        autoclose   : true,
        clearBtn    : true,
        keyboardNavigation : false,
        clear       : '閉じる',
        changeMonth : true,
        changeYear  : true,
        startDate   : myDate,
        endDate : date
    });
    $("#datepicker").datepicker("setDate", 'yesterday');
});