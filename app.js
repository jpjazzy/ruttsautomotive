'use strict';

/************** WORK LOG ********************
This is a log of the hours put into this website

11/12 - Scaffolding and basic structure for home page - 6 hours
11/13 - Getting images, CSS styling, adding Rutt's information - 6 Hours


********************************************/

/************* SLIDER JS ************************/
var slideIndex = 1;
showSlides(slideIndex);

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
