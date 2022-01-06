import gulp from 'gulp'
import merge from 'merge-stream'
import notifier from 'node-notifier'
import postcssImportUrl from 'postcss-import-url'
import gp from 'gulp-load-plugins'
const $ = gp()
import minimist from 'minimist'
import config from './config.json'

let options = minimist(process.argv.slice(2), config.knownOptions)

let stylesTasks = []
let scriptsTasks = []

// Compile sass
for (let task in config.styles) {
  stylesTasks.push('styles.' + task)

  gulp.task('styles.' + task, () => {
    return gulp.src(config.styles[task].src)
    .pipe($.if(!options.skipcache, $.newer({ dest: (config.styles[task].dst + config.styles[task].name), extra: config.styles[task].watch })))
    .pipe($.if(typeof config.styles[task].src === 'object', $.concat(config.styles[task].name)))
    .pipe($.postcss([postcssImportUrl({ modernBrowser: true })]))
    .pipe($.if(!options.production, $.sourcemaps.init()))
    .pipe($.sass({ outputStyle: (options.production ? 'compressed' : 'expanded') }))
    .on('error', $.notify.onError({ 'title': 'Sass Parse Error!', sound: true }))
    .pipe($.autoprefixer('last 2 version'))
    .pipe($.rename(config.styles[task].name))
    .pipe($.if(!options.production, $.sourcemaps.write('./')))
    .pipe($.size({
      title: 'styles.' + task,
      gzip: true,
    }))
    .pipe(gulp.dest(config.styles[task].dst))
    .pipe($.notify({ 'title': 'Sass compiled!', 'message': '<%= file.relative %>', sound: true, onLast: true }))
  })
};

// Compile js
for (let task in config.scripts) {
  scriptsTasks.push('scripts.' + task)

  gulp.task('scripts.' + task, [], () => {
    return gulp.src(config.scripts[task].src)
    .pipe($.if(!options.skipcache, $.newer(config.scripts[task].dst + config.scripts[task].name)))
    .pipe($.if(!options.production, $.sourcemaps.init()))
    .pipe($.if(typeof config.scripts[task].src === 'object', $.concat(config.scripts[task].name)))
    .pipe($.if(Boolean(config.scripts[task].babel), $.babel({ compact: false })))
    .on('error', $.notify.onError({ 'title': 'Babel Error!', sound: true }))
    .pipe($.include()) // include & require files after babel, ie only babelify my own files, not included vendor files
    .on('error', $.notify.onError({ 'title': 'Included files are missing!', sound: true }))
    .pipe($.if(options.production, $.uglify()))
    .pipe($.rename(config.scripts[task].name))
    .pipe($.if(!options.production, $.sourcemaps.write('./')))
    .pipe($.size({
      title: 'scripts.' + task,
      gzip: true,
    }))
    .pipe(gulp.dest(config.scripts[task].dst))
    .pipe($.notify({ 'title': 'Javascript compiled!', 'message': '<%= file.relative %>', sound: true, onLast: true }))
  })
}

// Optimize images
gulp.task('images', () => {
  let tasks = []

  for (let task in config.images) {
    let t = gulp.src(config.images[task].src)
    .pipe($.imagemin())
    .pipe(gulp.dest(config.images[task].dst))
    .pipe($.size({title: 'images'}))
    .pipe($.notify({ 'title': 'Images processed!', 'message': 'Done!', sound: true, onLast: true }))

    tasks.push(t)
  };

  return merge(tasks)
})

gulp.task('styles', stylesTasks, () => notifier.notify({ title: 'Compile Styles', message: 'Done!' }))
gulp.task('scripts', scriptsTasks, () => notifier.notify({ title: 'Compile Scripts', message: 'Done!' }))
gulp.task('default', ['styles', 'scripts'], () => notifier.notify({ title: (options.production ? 'Production' : 'Development') + ' Build', message: 'Done!' })) // [...stylesTasks, ...scriptsTasks]

gulp.task('watch', () => {
  for (let task in config.styles) {
    if (config.styles[task].watch) {
      gulp.watch(config.styles[task].watch, ['styles.' + task])
    }
  }

  for (let task in config.scripts) {
    if (config.scripts[task].watch) {
      gulp.watch(config.scripts[task].watch, ['scripts.' + task])
    }
  }
})
