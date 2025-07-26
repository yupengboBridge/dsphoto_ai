(function() {
  this.IndexController = (function() {
    IndexController.$inject = ['$location'];

    IndexController.prototype.page = {
      selfUrl: '-'
    };

    function IndexController($location) {
      this.$location = $location;
      this.init();
    }

    IndexController.prototype.init = function() {
      return this.page.selfUrl = this.$location.absUrl();
    };

    return IndexController;

  })();

}).call(this);

(function() {
  this.RichController = (function() {
    RichController.$inject = ['$location', 'SearchService', 'Pagination', 'constants'];

    RichController.prototype.page = {
      heiCode: '-',
      heiName: '-',
      courseId: '',
      dest: '',
      country: '',
      mainbrand: '',
      hitCount: 0,
      hitMessageEnabled: true,
      url: '-',
      urlEnabled: true,
      coursesEnabled: true,
      page: 1,
      courses: {},
      pagination: {},
      emsgRequired: '画像ファイル名を入力してください',
      emsgImgvalid: '正しい画像ファイル名を入力してください'
    };

    function RichController($location, search, pagination, constants) {
      this.$location = $location;
      this.search = search;
      this.constants = constants;
      this.init();
      this.page.pagination = pagination;
      this.searchByAll();
//      this.selectDest();
      // if(this.page.country){
      // this.selectCountry();
      // }
    }

    RichController.prototype.init = function() {
      this.page.page = 1;
      this.page.heiCode = this.$location.search().hei;
      this.page.heiName = this.constants.heiName;
      this.page.country = this.$location.search().p_country;
      return this.page.url = this.createUrl();
    };

    RichController.prototype.createUrl = function() {
      var parameters;
      var url = this.$location.absUrl();
      parameters = {};
      if (!this.empty(this.page.heiCode)) {
        parameters.hei = this.page.heiCode;
      }
      if (!this.empty(this.page.country)) {
        parameters.p_country = this.page.country;
      }
      if(0 < Object.keys(parameters).length){
          url = this.$location.absUrl().split('?')[0] + '?' + $.param(parameters);
      }
      return url;
    };

    RichController.prototype.empty = function(text) {
      return text === void 0 || text === '' || text === null;
    };

    RichController.prototype.destChange = function() {
          if(this.page.dest){
              this.selectCountry(); // 国プルダウン生成
          }else{
              setTimeout(function(){
                 $("#countrySel").html('<option value="">選択してください</option>');
             },100);
          }
          this.page.country = null;
    };
    RichController.prototype.searchByAll = function() {
          var parameters;
          this.page.page = 1;
          parameters = {
              hei: this.page.heiCode,
              page: 1,
              type: this.constants.type,
              country: this.page.country,
          };
          this.page.url = this.createUrl();
          return this.search.searchCourses(this.page, parameters, (function(_this) {
              return function(response, page) {
                  return _this.visualize(response, page);
              };
          })(this));
    };

    // RichController.prototype.selectDest = function() {
    //       var parameters;
    //       this.page.page = 1;
    //       parameters = {
    //           hei: this.page.heiCode,
    //           page: 1,
    //           type: this.constants.type,
    //
    //           country: null,
    //           mainbrand: this.page.mainbrand
    //       };
    //       this.page.url = this.createUrl();
    //       return this.search.searchSelectDest(this.page, parameters, (function(_this) {
    //           return function(response, page) {
    //               $("#destSel").html('<option value="" selected="">選択してください</option>');
    //               $.each(response.data.destination_code, function(key, value) {
    //                   var selected = page.dest == key ? 'selected' :''; // 初回表示
    //                   $("#destSel").append("<option value=" + key + " " + selected +">" + value +  "</option>");
    //               });
    //               return false;
    //           };
    //       })(this));
    // };


    RichController.prototype.selectCountry = function() {
          var parameters;
          this.page.page = 1;
          parameters = {
              hei: this.page.heiCode,
              page: 1,
              type: this.constants.type,
              country: null,
          };
          this.page.url = this.createUrl();
          return this.search.searchSelectCountry(this.page, parameters, (function(_this) {
              return function(response, page) {
                  $("#countrySel").html('<option value="" selected="">選択してください</option>');
                  $.each(response.data.country_code, function(key, value) {
                      var selected = page.country == key ? 'selected' :''; // 初回表示
                      $("#countrySel").append("<option value=" + key + " " + selected +">" + value +  "</option>");
                  });
                  return false;
              };
          })(this));
    };

    RichController.prototype.paginate = function(page) {
      var parameters;
      parameters = {
        hei: this.page.heiCode,
        page: page,
        type: this.constants.type,
        country: this.page.country,
      };
      this.page.page = page;
      return this.search.searchCourses(this.page, parameters, (function(_this) {
        return function(response, page) {
          return _this.visualize(response, page);
        };
      })(this));
    };

    RichController.prototype.visualize = function(response, page) {
      page.coursesEnabled = response.data.totalCount !== '0';
      page.pagination.createButtons(page.page, response.data.totalCount);
      page.hitCount = response.data.totalCount;
      return page.courses = response.data.contents;
    };

    RichController.prototype.createProductionUrl = function(heiCourseId) {
      return this.replaceHeiCourseId(this.constants.productionUrl, heiCourseId);
    };

    RichController.prototype.createInspectionUrl = function(heiCourseId) {
      return this.replaceHeiCourseId(this.constants.inspectionUrl, heiCourseId);
    };

    RichController.prototype.replaceHeiCourseId = function(text, heiCourseId) {
      var courseId, exploded, hei;
      exploded = heiCourseId.split('_');
      hei = exploded[0];
      courseId = exploded[1];
      return text.replace('{hei}', hei).replace('{courseId}', courseId).replace('{type}', this.constants.type);
    };

    return RichController;

  })();

}).call(this);

(function() {
  this.CourseIdFilter = function() {
    return function(element) {
//      return element.split('_')[1];
        return element;
    };
  };

}).call(this);

(function() {
  this.HeiFilter = function() {
    return function(element) {
      return element.split('_')[0];
    };
  };

}).call(this);

(function() {
  this.Course = (function() {
    function Course(photoNo, overview, expired) {
      this.photoNo = photoNo;
      this.overview = overview;
      this.expired = expired;
    }

    return Course;

  })();

}).call(this);

(function() {
  this.Pagination = (function() {
    Pagination.$inject = ['constants'];

    function Pagination(constants) {
      this.constants = constants;
    }

    Pagination.prototype.enabled = true;

    Pagination.prototype.buttons = [];

    Pagination.prototype.createButtons = function(page, totalCount) {
      var range, _i, _ref, _ref1, _results;
      this.initialize();
      this.activate(totalCount);
      if (this.isEmpty(totalCount)) {
        return;
      }
      range = this.createRange(page, totalCount);
      return angular.forEach((function() {
        _results = [];
        for (var _i = _ref = range.start, _ref1 = range.end; _ref <= _ref1 ? _i <= _ref1 : _i >= _ref1; _ref <= _ref1 ? _i++ : _i--){ _results.push(_i); }
        return _results;
      }).apply(this), function(v) {
        return this.push({
          number: v
        });
      }, this.buttons);
    };

    Pagination.prototype.initialize = function() {
      return this.buttons = [];
    };

    Pagination.prototype.activate = function(totalCount) {
      if (this.maxPage(totalCount) <= 1) {
        return this.enabled = false;
      } else {
        return this.enabled = true;
      }
    };

    Pagination.prototype.isEmpty = function(totalCount) {
      return totalCount === '0';
    };

    Pagination.prototype.createRange = function(page, totalCount) {
      var m, max, n;
      max = this.maxPage(totalCount);
      //n = page - this.constants.availablePageNumber;
      //m = page + this.constants.availablePageNumber;
      n = page - 2;
      m = page + 2;
      if (n < 1) {
        n = 1;
      }
      if (m > max) {
        m = max;
      }
      return {
        start: n,
        end: m
      };
    };

    Pagination.prototype.maxPage = function(totalCount) {
      var max, surplus;
      surplus = totalCount % this.constants.rpp;
      max = parseInt(totalCount / this.constants.rpp);
      if (surplus > 0) {
        max++;
      }
      return max;
    };

    return Pagination;

  })();

}).call(this);

(function() {
  this.SearchService = (function() {
    SearchService.inject = ['$http', '$httpParamSerializer', 'api'];

    function SearchService($http, $httpParamSerializer, api) {
      this.$http = $http;
      this.$httpParamSerializer = $httpParamSerializer;
      this.api = api;
    }

    SearchService.prototype.searchCourses = function(page, conditions, callback) {
      var query;
      $("#loadMask").show();
      query = this.$httpParamSerializer(conditions);
      return this.$http({
        url: this.api.search + '?' + query,
        method: 'GET'
      }).success(function(data, status, headers, config) {
         $("#loadMask").hide();
        if (data.errorCode === '0') {
          return callback(data, page);
        } else {
          return console.log(data, status, headers, config);
        }
      });
    };

    SearchService.prototype.searchSelectDest = function(page, conditions, callback) {
      var query;
      query = this.$httpParamSerializer(conditions);
      return this.$http({
        url: './api/destsel' + '?' + query,
        method: 'GET'
      }).success(function(data, status, headers, config) {
        if (data.errorCode === '0') {
          return callback(data, page);
        } else {
          return console.log(data, status, headers, config);
        }
      });
    };

    SearchService.prototype.searchSelectCountry = function(page, conditions, callback) {
      var query;
      query = this.$httpParamSerializer(conditions);
      return this.$http({
        url: './api/countrysel' + '?' + query,
        method: 'GET'
      }).success(function(data, status, headers, config) {
        if (data.errorCode === '0') {
          return callback(data, page);
        } else {
          return console.log(data, status, headers, config);
        }
      });
    };

    return SearchService;

  })();

}).call(this);

(function() {
    this.DownloadController = (function() {
        DownloadController.$inject = ['$location'];

        DownloadController.prototype.page = {
            selfUrl: '-'
        };

        function DownloadController($location) {
            this.$location = $location;
            this.init();
        }

        DownloadController.prototype.init = function() {
            return this.page.selfUrl = this.$location.absUrl();
        };

        return DownloadController;

    })();

}).call(this);

$(document).ready(function(){
    if(location.href.match(/limit/)){
        $(".dp-sub-title").hide();
    }
});
