<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Backend\Classes\FormField;
use DMA\Friends\Models\Location;
use RainLab\User\Models\User;
use DMA\Friends\Classes\PrintManager;
use Flash;
use Lang;
use SystemException;


/**
 * Activity Type Widget
 * 
 * This widget provides form elements for managing 
 * custom Activities implemented by friends and 3rd party plugins 
 * 
 * @package dma\friends
 * @author Kristen Arnold
 */
class PrintMembershipCard extends FormWidgetBase
{
    public $previewMode = false;

        /**
     * @var string If the field element names should be contained in an array.
     * Eg: <input name="nameArray[fieldName]" />
     */
    public $arrayName = true;

    /** 
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Print Membership Card',
            'description' => 'Prints a new membership card'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return FormField::NO_SAVE_DATA;
    }

    /** 
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('widget');
    }

    /**
     * {@inheritDoc}
     */
    public function prepareVars()
    {
        $locations = Location::hasMemberPrinter()->groupBy('printer_membership')->get();

        $options[] = '<option value="">Select One</option>';

        foreach ($locations as $location) {
            $options[] = '<option value="'. $location->id . '">'. $location->printer_membership . '</option>';
        }

        $this->vars['locationOptions'] = $options;
    }

    public function onPrintCard()
    {
        $locationId = post('printerLocation');
        if (empty($locationId)) {
            Flash::error(Lang::get('dma.friends::lang.user.memberCardLocation'));
            return;
        }

        $location = Location::find($locationId);

        $user = post('User');
        $user = User::where('email', '=', $user['email'])->first();

        try { 
            $manager = new PrintManager($location, $user);
            $manager->printIdCard();
            Flash::info(Lang::get('dma.friends::lang.user.memberCard', ['title' => $location->title]));
        } catch(SystemException $e) {
            Flash::error($e->getMessage());
        }
    }
}
