'use strict';

var Shuffle = window.Shuffle;

var Filter = function (element) {
	this.categories = Array.from(document.querySelectorAll('#shuffle-filters option'));
    console.log(this.categories)

	this.shuffle = new Shuffle(element, {
		easing: 'cubic-bezier(0.165, 0.840, 0.440, 1.000)', // easeOutQuart
		itemSelector: '.search-row',
		sizer: '.the-sizer',
		isCentered : false
	});

	this.filters = {
		categories: [],
	};

	this._bindEventListeners();
};

/**
 * Bind event listeners for when the filters change.
 */
Filter.prototype._bindEventListeners = function () {
	this._onCategoriesChange = this._handleCategoriesChange.bind(this);

    $('#shuffle-filters').on('change', this._onCategoriesChange)
	/*this.categories.forEach(function (button) {
		button.addEventListener('click', this._onCategoriesChange);
	}, this);*/
};


Filter.prototype._getCurrentCategoriesFilters = function () {
    let getActive = $('#shuffle-filters option:selected');
    return getActive.attr('data-group')


	/*return this.categories.filter(function (button) {
		return button.classList.contains('active');
	}).map(function (button) {
		return button.getAttribute('data-categories');
	});*/
};


Filter.prototype._handleCategoriesChange = function (evt) {
	var button = evt.currentTarget;
	// Treat these buttons like radio buttons where only 1 can be selected.

    let getActive = $('#shuffle-filters option:selected');
    this.filters.categories = this._getCurrentCategoriesFilters();
	this.filter();

	/*if (button.classList.contains('active')) {
		button.classList.remove('active');
	} else {
		this.categories.forEach(function (btn) {
		btn.classList.remove('active');
		});

		button.classList.add('active');
	}

	this.filters.categories = this._getCurrentCategoriesFilters();
	this.filter();*/
};

/**
 * Filter shuffle based on the current state of filters.
 */
Filter.prototype.filter = function () {
	if (this.hasActiveFilters() && this.filters.categories != 'none') {
		this.shuffle.filter(this.itemPassesFilters.bind(this));	
	} else {
		this.shuffle.filter(Shuffle.ALL_ITEMS);
	}
};

/**
 * If any of the arrays in the `filters` property have a length of more than zero,
 * that means there is an active filter.
 * @return {boolean}
 */
Filter.prototype.hasActiveFilters = function () {
    console.log(this.filters)
	return Object.keys(this.filters).some(function (key) {
		return this.filters[key].length > 0;
	}, this);
};

/**
 * Determine whether an element passes the current filters.
 * @param {Element} element Element to test.
 * @return {boolean} Whether it satisfies all current filters.
 */
Filter.prototype.itemPassesFilters = function (element) {
	var categories = this.filters.categories;
	var categoriesElement = $(element).attr('data-group');
	//categoriesElement.split(',');
	

	/*if (categories.length > 0 ) {
		for (let i = 0; i < categories.length; i++) {
			const element = categories[i];
			if(!categories.includes(element)) return false
			else return true;
		}
		//if(!categories.includes(category)) return false;
	}*/

	if(categories.length > 0){
		if(categories == categoriesElement) return true;
		else return false;
	}


	return true;
};

document.addEventListener('DOMContentLoaded', function () {
	window.Filter = new Filter(document.querySelector('.search-grid'));
    //console.log(document.querySelector('.search-grid > .search-row'))
});
