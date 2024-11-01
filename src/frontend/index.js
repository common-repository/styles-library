import './styles.scss';

let toggle_easl_accordion = (el) => {
	let accordion =	el.currentTarget.closest('.ea-block-accordion');

	if(accordion) {
		accordion.classList.toggle('ea-active')
		el.currentTarget.ariaPressed = el.currentTarget.ariaPressed == 'true' ? 'false' : 'true'
	}
}
let move_easl_slider_item = (dir, el) => {
	let container =	el.currentTarget.closest('.ea-item-slider-container');
	let slider = container.querySelector('.ea-item-slider')

	//extra step if the slider is a group block
	if(slider.classList.contains('wp-block-group') && slider.classList.contains('easl-wp-version-below-6')) {
		slider = slider.firstElementChild;
	}

	//getting the first element of the slider
	let elem = slider;
	if ( slider.childNodes.length > 0 ) {
		elem =  slider.firstElementChild;
	}

	let scrollLeft     = slider.scrollLeft,
		width          = elem.offsetWidth,
		scrollWidth    = slider.scrollWidth,
		containerWidth = slider.offsetWidth;

	if(dir) {
		if ( scrollWidth - scrollLeft - containerWidth < 1 ) {
			//At the end, let's go back to first item
			let firstItem = slider.firstElementChild;
			slider.scrollTo({
				left: 0,
				behavior: 'smooth'
				});
		} else {
			slider.scrollBy({
				left: width,
				behavior: 'smooth'
				});
		}
	} else {
		if ( scrollLeft > 1 ) {
			//can go left
			slider.scrollBy({
				left: width * -1,
				behavior: 'smooth'
				});
		}
	}
}
document.addEventListener('DOMContentLoaded', function(event) {
	let sliders = document.getElementsByClassName('ea-item-slider');
	if(sliders)
	[...sliders].map((item) => {
		//Wordpress 6.0 changes some blocks html markup
		let block_classes = item.classList
		if( block_classes.contains('wp-block-group')) {
			let first_child = item.firstElementChild
			if(first_child.classList.contains('wp-block-group__inner-container') ) {
				item.classList.add('easl-wp-version-below-6')
			}
		}
	})
	let sliders_next = document.getElementsByClassName('ea-item-slider-control-next');
	[...sliders_next].map((item) => {
		item.setAttribute('role','button')
		item.setAttribute('tabindex','0')
		item.addEventListener('pointerdown', (el) => {
			el.preventDefault();
			move_easl_slider_item(true,el)
			return false;
		});

		item.addEventListener('keydown', (el) => {
			if(el.keyCode == '39') {
				el.preventDefault();
				move_easl_slider_item(true,el)
			}
		});
	})

	let sliders_prev = document.getElementsByClassName('ea-item-slider-control-prev');
	[...sliders_prev].map((item) => {
		item.setAttribute('role','button')
		item.setAttribute('tabindex','0')
		item.addEventListener('pointerdown', (el,evt) => {
			el.preventDefault();
			move_easl_slider_item(false,el)
		});
		item.addEventListener('keydown', (el) => {
			if(el.keyCode == '37') {
				el.preventDefault();
				move_slider_item(false,el)
			}
		});
	})

  let accordion_btns = document.getElementsByClassName('ea-block-accordion-btn');
  [...accordion_btns].map((item) => {
    item.setAttribute('role','button')
    item.setAttribute('tabindex','0')
    item.setAttribute('aria-pressed','false')

    item.addEventListener('pointerdown', (el) => {
      toggle_easl_accordion(el)

    });

    item.addEventListener('keydown', (el) => {
      if(el.keyCode == '38' || el.keyCode == '40') {
        el.preventDefault();
        toggle_easl_accordion(el)
      }
    });
  })
});
