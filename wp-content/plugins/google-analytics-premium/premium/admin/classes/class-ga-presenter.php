<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Presenter
 */
abstract class Yoast_GA_Presenter {

	/**
	 * Present the HTML that should be presented to the user.
	 *
	 * @return string
	 */
	abstract protected function render_view();

	/**
	 * Call render view to print the view.
	 */
	public function print_view() {
		echo $this->render_view();
	}

}