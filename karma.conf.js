// Karma configuration
// Generated on Thu Sep 05 2013 23:59:36 GMT+0200 (CEST)

module.exports = function(config) {
  config.set({

    // base path, that will be used to resolve files and exclude
    basePath: 'vreasy/public',

    // frameworks to use
    frameworks: ['jasmine'],

    preprocessors: {
      'taskConfirmationApp/templates/**/*.html': 'ng-html2js'
    },

    ngHtml2JsPreprocessor: {
        moduleName: 'taskConfirmationApp',
        stripPrefix: 'public/',
    },

    reportSlowerThan: 50,

    // list of files / patterns to load in the browser
    files: [
        'bower_components/angular/angular.min.js',
        'bower_components/underscore/underscore-min.js',
        'bower_components/angular-resource/angular-resource.min.js',
        'bower_components/angular-mocks/angular-mocks.js',
        'taskConfirmationApp/app.js',
        '../../tests/angular/*.js',
    ],

    // test results reporter to use
    // possible values: 'dots', 'progress', 'junit', 'growl', 'coverage'
    reporters: ['progress', 'growl'],


    // web server port
    port: 9876,


    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes

    autoWatch: true,

    // Start these browsers, currently available:
    // - Chrome
    // - ChromeCanary
    // - Firefox
    // - Opera
    // - Safari (only Mac)
    // - PhantomJS
    // - IE (only Windows)
    browsers: ['PhantomJS'],


    // If browser does not capture in given timeout [ms], kill it
    captureTimeout: 60000,


    // Continuous Integration mode
    // if true, it capture browsers, run tests and exit
    singleRun: false
  });
};
