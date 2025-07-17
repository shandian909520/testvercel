function _asyncToGenerator(fn) {
    return function() {
        var gen = fn.apply(this, arguments);
        return new Promise(function(resolve, reject) {
            function step(key, arg) {
                try {
                    var info = gen[key](arg);
                    var value = info.value;
                } catch (error) {
                    reject(error);
                    return;
                }
                if (info.done) {
                    resolve(value);
                } else {
                    return Promise.resolve(value).then(function(value) {
                        step("next", value);
                    }, function(err) {
                        step("throw", err);
                    });
                }
            }
            return step("next");
        });
    };
}

var ci = require("miniprogram-ci");

_asyncToGenerator(/* */ regeneratorRuntime.mark(function _callee() {
    var project, uploadResult;
    return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                project = new ci.Project({
                    appid: "wx125625b6d62285c3",
                    type: "miniProgram",
                    projectPath: "/www/wwwroot/fabu.xjcms.cc/wechatmp",
                    privateKeyPath: "/www/wwwroot/fabu.xjcms.cc/public/wekey/wxb35185328f917ee0.key",
                    ignores: [ "node_modules/**/*" ]
                });
                _context.next = 3;
                return ci.upload({
                    project: project,
                    version: "3.03",
                    desc: "网捷认证服务助手",
                    setting: {
                        es6: true
                    },
                    onProgressUpdate: console.log
                });

              case 3:
                uploadResult = _context.sent;
                console.log(uploadResult);

              case 5:
              case "end":
                return _context.stop();
            }
        }
    }, _callee, undefined);
}))();