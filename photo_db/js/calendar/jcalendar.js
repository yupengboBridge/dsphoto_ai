// 入力された値が日付でYYYY/MM/DD形式になっているか調べます
function ckDate(datestr)
{
	// 正規表現により、書式をチェックします。
	if (!datestr.match(/^\d{4}\/\d{2}\/\d{2}$/))
	{
		return false;
	}

	// 年月日にそれぞれ分割します。
	var vYear = datestr.substr(0, 4) - 0;
	var vMonth = datestr.substr(5, 2) - 1; 					// Javascriptは、0-11で表現
	var vDay = datestr.substr(8, 2) - 0;

	// 月,日の妥当性チェック
	if (vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31)
	{
		var vDt = new Date(vYear, vMonth, vDay);
		if (isNaN(vDt))
		{
			return false;
		}
		else if (vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay)
		{
			return true;
		}
		else
		{
			return false;
		}
	}else{
		return false;
	}
}

// 日本語のカレンダーを作成します。
function create_jcalendar(calID, from_date, to_date, sel_date)
{
	// 表示する範囲を決定します。
	var dateRange = { mindate:"", maxdate:"", pages:1, selected:""};
	if (from_date.length != 0)
	{
		//from_date = from_date.substr(5, 5) + "/" + from_date.substr(0, 4);
		dateRange['mindate'] = from_date;
	}
	else
	{
		dateRange['mindate'] = "01/01/2000";
	}
	
	if (to_date.length != 0)
	{
		//to_date = to_date.substr(5, 5) + "/" + to_date.substr(0, 4);
		dateRange['maxdate'] = to_date;
	}
	else
	{
		dateRange['maxdate'] = "12/31/2100";
	}
	
	if (sel_date.length != 0)
	{
		dateRange['selected'] = sel_date;
	}
	var calObj = new YAHOO.widget.Calendar(calID, calID, dateRange);

//	// 表示するページ数を設定します。
//	if (disp_page > 1)
//	{
//		dateRange['pages'] = disp_page;
//	}
//
//	// カレンダーのインスタンスを生成します。
//	if (disp_page <= 1)
//	{
//		// １カレンダー
//		var calObj = new YAHOO.widget.Calendar(calID, calID, dateRange);
//	}
//	else
//	{
//		// ２カレンダー以上
//		var calObj = new YAHOO.widget.CalendarGroup(calID, calID, dateRange);
//	}

	// タイトルを設定します。（ドラッグできるようにdivタグを書いておきます。）
    //calObj.cfg.setProperty("title", "<div id='handle'>タイトル</div>");

	calObj.cfg.setProperty("MONTHS_SHORT", ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"]);
	calObj.cfg.setProperty("MONTHS_LONG", ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"]);
	calObj.cfg.setProperty("WEEKDAYS_1CHAR", ["<font color='red'>日</font>", "月", "火", "水", "木", "金", "<font color='blue'>土</font>"]);
	calObj.cfg.setProperty("WEEKDAYS_SHORT", ["<font color='red'>日</font>", "月", "火", "水", "木", "金", "<font color='blue'>土</font>"]);
	calObj.cfg.setProperty("WEEKDAYS_MEDIUM",["<font color='red'>日</font>", "月", "火", "水", "木", "金", "<font color='blue'>土</font>"]);
	calObj.cfg.setProperty("WEEKDAYS_LONG", ["日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日"]);
	calObj.cfg.setProperty("MY_YEAR_POSITION", 1);
	calObj.cfg.setProperty("MY_MONTH_POSITION", 2);
	calObj.cfg.setProperty("MDY_YEAR_POSITION", 1);
	calObj.cfg.setProperty("MDY_MONTH_POSITION", 2);
	calObj.cfg.setProperty("MDY_DAY_POSITION", 3);
	calObj.cfg.setProperty("MY_LABEL_YEAR_POSITION",1);
	calObj.cfg.setProperty("MY_LABEL_MONTH_POSITION",2);
	calObj.cfg.setProperty("MY_LABEL_YEAR_SUFFIX","年");
	calObj.cfg.setProperty("MY_LABEL_MONTH_SUFFIX","");

	calObj.cfg.setProperty("MULTI_SELECT", false);
    calObj.cfg.setProperty("close", false);
    calObj.cfg.setProperty("START_WEEKDAY", 0);

	// 休日を表示します。
	// 祝日を表示するためのイベントを登録します。
	calObj.renderEvent.subscribe(show_holidays, calObj, true);

//	// 休日を表示します。
//	if (disp_page <= 1)
//	{
//		// 祝日を表示するためのイベントを登録します。
//		calObj.renderEvent.subscribe(show_holidays, calObj, true);
//	}
//	else
//	{
//		// 現在の休日表示イベントはシングルカレンダーのみに対応しています。
//		// 固定タイプの休日表示を利用します。
//		set_holidays_fixed(calObj);
//	}

	// カレンダーを表示します。
	calObj.render();
	calObj.hide();


	return calObj;
}

// SELECTの値をセットします。
function set_select_value(selId, value)
{
	sel = document.getElementById(selId);

	for (var i = 0; i < sel.length; i++)
	{
		if (sel.options[i].text == value)
		{
			sel.options[i].selected = true;
			return;
		}
	}
}

// 暫定版の休日表示関数です。
function set_holidays_fixed(calID)
{
	calID.addRenderer('2008/7/21', renderSunday);
	calID.addRenderer('2008/9/15', renderSunday);
	calID.addRenderer('2008/9/23', renderSunday);
}

// －－イベントを定義します。－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
// 休日をセットします。
var renderSunday = function(cal, cell)
{
	YAHOO.util.Dom.addClass(cell, "wd0");
};

// 休日を表示します。
// 　※シングルカレンダーのみに対応しています。
var show_holidays = function(type, args, obj)
{
	// GCalHolidays.jsを読み込んでいるかチェックします。
    if (!window.GCalHolidays)
    {
    	// 読み込んでいない場合は、戻ります。
    	return;
    }

    // 表示しているページの年月を取得します。
	var elm = document.getElementById('output');
	var target = this.cfg.getProperty("pagedate");

	// 祝日をグーグルに問い合わせます。
	// 問い合わせ終了時に、set_holidaysをコールバックします。
    GCalHolidays.get(set_holidays, target.getFullYear(), target.getMonth() + 1);
};

// 祝日をセットします。
// 　GCalHolidays.jsを利用しています。
// 　※GCalHolidays.getのコールバックイベントです。
// 　　シングルカレンダーのみに対応しています。
var set_holidays = function(holidays)
{
	// 祝日が設定されているかどうかをチェックします。
    if (holidays.length === 0) {
        return;
    }

    // 該当年月のままかを確認します。
    // （コールバックで呼ばれるまでに、移動している恐れがあるためです。）
    var first = holidays[0];
    var table = YAHOO.util.Dom.getElementsByClassName("y" + first.year, "table", this.place)[0];
    var tbody = YAHOO.util.Dom.getElementsByClassName("m" + first.month, "tbody", table)[0];
    if (!table || !tbody) {
        return;
    }

    // 祝日を設定します。
    for (i=0 ; i < holidays.length ; i++)
    {
        var h = holidays[i];
        var td = YAHOO.util.Dom.getElementsByClassName("d" + h.date, "td", tbody)[0];
        YAHOO.util.Dom.addClass(td, "wd0");
		//マウスオーバーで祝日名を表示
        td.title = h.title;
    }
};


