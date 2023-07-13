$(document).ready(function(){
  $(".nav-tabs li").each(function(i,val) {
    let elem = $(this).find('a');
    let href = $(elem).attr('href');
    let flag = 1;
    if(href !== '#tab-discount' && href !== '#tab-special'){
      $(elem).addClass('hide');
    }
    if(href === '#tab-discount'){
      $(elem).trigger('click');
    }
  });
});
