<?php

class Pager {

	public  $m_count;    // количество страниц
	public  $m_size;     // количество на странице
	public  $m_current;  // текущая страцица
	private $maxButtonCount;
	private $url;

	const CSS_FIRST_PAGE    = '';
	const CSS_LAST_PAGE     = '';
	const CSS_PREVIOUS_PAGE = '';
	const CSS_NEXT_PAGE     = '';
	const CSS_INTERNAL_PAGE = '';
	const CSS_HIDDEN_PAGE   = '';
	const CSS_SELECTED_PAGE = 'active';

	function __construct($__count, $__size, $__current = 0) {
		$this->m_count = (int)ceil((float)$__count / (float)$__size);
		$this->m_count = max(1, $this->m_count);
		$this->m_size = $__size;
		$this->m_current = min(max($__current, 1), $this->m_count);

		$this->maxButtonCount = 15;
	}

	public function setUrl($__url) {
		$this->url = $__url;
	}

	protected function createButtons() {
		$buttons = array();
		$currentPage = $this->m_current;
		$pageCount = $this->m_count;

		$first = '<i class="glyphicon glyphicon-fast-backward"></i>';
		$last = '<i class="glyphicon glyphicon-fast-forward"></i>';
		$prev = '<i class="glyphicon glyphicon-chevron-left"></i>';
		$next = '<i class="glyphicon glyphicon-chevron-right"></i>';

		list($beginPage, $endPage) = $this->getPageRange();

		// first
		$buttons[] = $this->createPageButton($first, 1, self::CSS_FIRST_PAGE, false, false);

		// prev page
		$buttons[] = $this->createPageButton($prev, max($currentPage - 1, 1), self::CSS_PREVIOUS_PAGE, $currentPage - 1 <= 0, false);

		// internal pages
		for ($i = $beginPage; $i <= $endPage; ++$i)
			$buttons[] = $this->createPageButton($i, $i, self::CSS_INTERNAL_PAGE, false, $i == $currentPage);

		// next page
		$buttons[] = $this->createPageButton($next, min($currentPage + 1, $pageCount), self::CSS_NEXT_PAGE, $currentPage + 1 > $pageCount, false);

		// last page
		$buttons[] = $this->createPageButton($last, $pageCount, self::CSS_LAST_PAGE, false, false);

		return $buttons;
	}

	protected function getPageRange() {
		$currentPage = $this->m_current;
		$pageCount = $this->m_count;

		$beginPage = max(1, $currentPage - (int)($this->maxButtonCount / 2));
		if (($endPage = $beginPage + $this->maxButtonCount) >= $pageCount) {
			$endPage = $pageCount;
			$beginPage = max(1, $endPage - $this->maxButtonCount + 1);
		}
		return array($beginPage, $endPage);
	}

	protected function createPageButton($label, $page, $class, $hidden, $selected) {

		if ($hidden || $selected)
			$class .= ' ' . ($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);

		return sprintf('<li class="%s"><a href="%s">%s</a></li>', $class, "{$this->url}page/$page/", $label);
	}

	public function draw() {

		$buttons = $this->createButtons();
		if (empty($buttons))
			return;

		return '<ul class="pagination pagination-sm">' . implode(PHP_EOL, $buttons) . '</ul>';
	}

}
