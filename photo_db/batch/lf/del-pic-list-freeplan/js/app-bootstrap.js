(function() {
  var app;

  app = angular.module('rich', []);

  app.config(function($interpolateProvider, $locationProvider) {
    $interpolateProvider.startSymbol('[[').endSymbol(']]');
    return $locationProvider.html5Mode({
      enabled: true,
      requireBase: false
    });
  });

    app.config(function($interpolateProvider, $locationProvider) {
        $interpolateProvider.startSymbol('[[').endSymbol(']]');
        return $locationProvider.html5Mode({
            enabled: true,
            requireBase: false
        });
    });

    app.directive("flSlide", function($timeout, $http, $httpParamSerializerJQLike){
        var cnt = 1;
        return function(scope, element, attrs){
            scope.onclick = function () {
                cnt = 0;
                // 各値の取得
                var fst = element.data('fst');
                var snd = element.data('snd');
                var ex = element.data('ex');
                var course = element.data('cos');
                var ctg = element.data('ctg');
                var ctgid = element.data('ctgid');
                var hei = element.data('hei');

                //modal dialogの呼び出し
                $timeout(function () {
                    $("#modalTrigger").trigger('click');
                }, 0);
                //modal dialogの初期化
                $("#modalName").val($("#spanName" + fst).html());
                // 各値の保存
                $("#set").data('fst', fst);
                $("#set").data('snd', snd);
                $("#set").data('ex', ex);
                $("#set").data('course', course);
                $("#set").data('category', ctg);
                $("#set").data('ctgid', ctgid);
                $("#set").data('hei', hei);
            };
            $("#set").click(function() {
                // 各値の取得
                var fst = $(this).data('fst');
                var snd = $(this).data('snd');
                var ex = $(this).data('ex');
                var courseval = $(this).data('course');
                var ctg = $(this).data('category');
                var ctgid = $(this).data('ctgid');
                var hei = $(this).data('hei');
                var imgst = $("#modalName").val();
                // エスケープ処理
                var course = selectorEscape(courseval);
                var courseurl = encodeURIComponent(courseval);

                // フルパス対応
                var rep = imgst.match(/[0-9a-zA-Z\-_]*\.(jpg|gif|png)$/);
                $('#img-no-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).text(rep[0]);
                $('#date-comp-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).show();
                //$("#img-" + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).attr("src","http://x.hankyu-travel.com/cms_photo_image/image_search_kikan3.php?p_photo_mno=" + $("#modalName").val());
                $("#img-" + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).attr("src","http://x.hankyu-travel.com/cms_photo_image/image_search_kikan3.php?p_photo_mno=" + rep[0]);
                //$('#btn-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).text("再度更新する" + fst + "-" + snd + "-" + ex);
                $('#btn-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).text("再度更新する");
                if(cnt === 0) {
                    $.ajax({
                        type: "GET",
                        async: true,
                        cache: false,
                        url: './api/del' + '?img=' + rep[0] + '&fstNm=' + fst + '&sndNm=' + snd + '&exNm=' + ex + '&courseId=' + courseurl + '&hei=' + hei + '&type=' + constants.type + '&ctgId=' + ctgid,
                        beforeSend: function (jqXHR) {
                            cnt++;
                            return true;
                        },
                        success: function (data, text_status, xhr) {
                            scope.result = data;
                            var res = eval('('+data+')');
                            if (res.errorCode === '0') {
                                $('#day-no-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).text(res.data.deletionDate);
                                $('#day-no-' + course + "-" + ctg + "-" + fst + "-" + snd + "-" + ex).css('background-color', '#039');
                                return console.log(data);
                            }else {
                                return console.log(data);
                            }
                        },
                        error: function (xhr, text_status, err_thrown) {
                            // エラー処理（通信失敗時のみ）
                            scope.result = '!!通信に失敗しました!!';
                            return;
                        }
                    });
                }
            });
        }
    });

  app.filter('CourseIdFilter', CourseIdFilter);

  app.filter('HeiFilter', HeiFilter);

  app.controller('IndexController', IndexController);

  app.controller('DownloadController', DownloadController);

  app.controller('RichController', RichController);

  app.service('SearchService', SearchService);

  app.service('Pagination', Pagination);

  app.value('api', {
    search: './api/search'
  });

  if (typeof constants !== "undefined" && constants !== null) {
    app.value('constants', constants);
  }
    function selectorEscape(val){
        val = String(val);
        var res = val.match(/[ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g);
        if(res !== null){
            return val.replace(/[ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g, '\\$&');
        }
        return val;
    }
}).call(this);
