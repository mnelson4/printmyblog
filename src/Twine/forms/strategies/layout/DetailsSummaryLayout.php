<?php


namespace Twine\forms\strategies\layout;


use Twine\forms\base\FormSectionBase;
use Twine\forms\base\FormSectionDetails;
use Twine\forms\base\FormSectionProper;
use Twine\forms\inputs\FormInputBase;
use Twine\helpers\Html;

class DetailsSummaryLayout extends FormSectionLayoutBase {
	/**
	 * @var FormSectionBase
	 */
	protected $inner_layout;

	/**
	 * DetailsSummaryLayout constructor.
	 *
	 * @param FormSectionLayoutBase $inner_layout
	 */
	public function __construct(FormSectionLayoutBase $inner_layout) {
		$this->inner_layout = $inner_layout;
		parent::__construct();
	}

	public function _construct_finalize( FormSectionProper $form ) {
		$this->inner_layout->_construct_finalize($form);
		parent::_construct_finalize( $form );
	}

	public function layout_form_begin() {
		$html_generator = Html::instance();
		if($this->_form_section instanceof FormSectionDetails){
			$summary = $this->_form_section->getSummary();
		} else {
			$summary = __('Show Options', 'print-my-blog');
		}

		return $this->display_form_wide_errors()
		       . $html_generator->openTag(
				'details',
				$this->_form_section->html_id(),
				$this->_form_section->html_class() . ' twine-details',
				$this->_form_section->html_style()
			) . $html_generator->tag(
				'summary',
				$summary,
				$this->_form_section->html_id() . '-summary',
				'twine-summary'
			) . $this->inner_layout->layout_form_begin() . $this->inner_layout->layout_form_loop();
	}

	public function layout_form() {
		return parent::layout_form(); // TODO: Change the autogenerated stub
	}

	public function layout_form_end() {
		$html_generator = Html::instance();
		return $this->inner_layout->layout_form_end() . $html_generator->closeTag('details');
	}

	public function layout_input( $input ) {
		$this->inner_layout->layout_input($input);
	}

	public function layout_subsection( $subsection ) {
		$this->inner_layout->layout_subsection($subsection);
	}
}