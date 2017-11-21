'use strict';

/************** WORK LOG ********************
This is a log of the hours put into this website

11/12 - Scaffolding and basic structure for home page - 6 hours
11/13 - Getting images, CSS styling, adding Rutt's information - 6 Hours
11/14 - Revamped man page and added more of rutts information - 5 Hours
11/15 - Added sup pages and started inputting information - 2 hours
11/16 - Added secondary page information and stylings - 3.5 hours
11/17 - Added autoplay to slider, added about us information, further styled app - 2 hour
11/19 - Added Tech images to about me section and updated some styling - 1 hr
11/20 - Styled and input tech about us section and other about page information - 2 hours

********************************************/

/************* SLIDER JS ************************/
var slideIndex = 1;
showSlides(slideIndex);

var transitionSpeed = 8000;

var autoplay = setInterval(function() {plusSlides(1);}, transitionSpeed);

function userClickPlusSlides(n) {
  clearInterval(autoplay);
  plusSlides(n);
  autoplay = setInterval(function() {plusSlides(1);}, transitionSpeed);
}

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName('mySlides');
  if (n > slides.length) {slideIndex = 1;}
  if (n < 1) {slideIndex = slides.length;}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = 'none';
  }
  slides[slideIndex - 1].style.display = 'block';
}
