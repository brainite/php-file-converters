function slideshow(s) {
  var $ = jQuery;
  var $carousel = $('<div id="slideshow" class="carousel slide" data-ride="carousel">').appendTo($('body'));
  var $indicators, $list, i, slide, $item, $caption;

  // Configure based on the mode and settings.
  var conf = {
    'indicators' : true,
    'controls' : true,
    'captions' : true,
    // carousel options
    'interval' : 5000,
    'pause'    : 'hover',
    'wrap'     : true,
    'keyboard' : true
  };
  switch (s.mode) {
  case 'schedule':
    conf.indicators = conf.controls = conf.interval = false;
    conf.pause = null;
    for (i = 0; i < s.items.length; ++i) {
      s.items[i]['beginms'] = Date.parse(s.items[i]['begin']);
    }
    break;
  }
  for (i in conf) {
    if (typeof s[i] != 'undefined') {
      conf[i] = s[i];
    }
  }
  
  // Build the shell
  if (conf.indicators) {
    $indicators = $('<ol class="carousel-indicators">').appendTo($carousel);
  }
  $list = $('<div class="carousel-inner" role="listbox">').appendTo($carousel);
  if (conf.controls) {
    $carousel.append('<a class="left carousel-control" href="#slideshow" role="button" data-slide="prev"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span><span class="sr-only">Previous</span></a>');
    $carousel.append('<a class="right carousel-control" href="#slideshow" role="button" data-slide="next"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span><span class="sr-only">Next</span></a>');
  }
  
  // Add the slides
  for (i = 0; i < s.items.length; ++i) {
    slide = s.items[i];
    if (conf.indicators) {
      $indicators.append('<li data-target="#slideshow" data-slide-to="0"' + (i ? '' : ' class="active"') + '></li>');
    }
    $item = $('<div class="item">').appendTo($list);
    if (!i) {
      $item.addClass('active');
    }
    $item.append($('<img />').attr('src', 'img/slide' + slide.number + '.jpg').attr('alt', slide.title));
    if (conf.captions) {
      $caption = $('<div class="carousel-caption">').appendTo($item);
      $caption.append($('<h3>').text(slide.title));
      if (typeof slide.description != 'undefined') {
        $caption.append($('<p>').text(slide.description));
      }
    }
  }
  
  // Launch the carousel
  $carousel.carousel(conf);
 
  // Implement the schedule.
  switch (s.mode) {
  case 'schedule':
    setInterval(function(){
      // Select the slide to show.
      var ms = (new Date()).getTime();
      for (i = 0; i < s.items.length; ++i) {
        if (s.items[i].beginms > ms) {
          $carousel.carousel(Math.max(0, i - 1));
          return;
        }
      }
      $carousel.carousel(s.items.length - 1);
    }, 5000);
    break;
  }
}
