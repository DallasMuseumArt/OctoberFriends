<?php namespace DMA\Friends\Classes;

use RainLab\User\Models\User;
use DMA\Friends\Models\Location;
use DMA\Friends\Models\Reward;
use DMA\Friends\Models\Settings;
use Lang;
use Cms\Classes\Theme;
use System\Classes\SystemException;

require_once('fpdf17/code39.php');

/**
 * Class for managing the printing of membership cards and coupons
 * @package DMA\Friends
 * @author Kristen Arnold, Carlos Arroyo
 */ 
class PrintManager
{
    protected $pdf;       // PDF Engine  
    protected $location;  // The location model
    protected $user;      // The user model
    public $logo = '/assets/images/printer_logo.png'; // Path to print logo inside of the active theme
    
    /**
     * @param Location $location
     * A location model that contains information about printers
     * @param User $user
     * The user model that is trying to print
     */
    public function __construct(Location $location, User $user)
    {
        $this->location = $location;
        $this->user = $user;
    }

    /**
     * Initialize the PDF object, this MUST be called for all print methods
     * @param integer $width
     * The width of the pdf to print
     * @param integer $height
     * The height of the pdf to print
     * @param string $orientation
     * One of two values
     * - "P" to print in portrait mode
     * - "L" to print in landscape mode
     */
    public function init($width, $height, $orientation)
    {
        if (!$width || !$height || !$orientation)
            throw new SystemException(Lang::get('dma.friends::lang.exceptions.printerSettingsMissing'));
        
        $this->pdf = new \PDF_Code39($orientation, "mm", [ $height, $width]);
        $this->pdf->AddPage();
    }
    
    public function doPrint($printer)
    {
        $fn = "/tmp/" . md5(date("Y-m-d-h-i-s-u")) . ".pdf";
        $this->pdf->Output($fn, "F");

        exec("lp -d " . $printer . " " . $fn);
        @unlink($fn);
    }

    /**
     * Print an identification card for a member
     */
    public function printIdCard()
    {
        $this->init(
            Settings::get('membership_width'), 
            Settings::get('membership_height'), 
            Settings::get('membership_orientation')
        );

        $name = $this->user->metadata->first_name . ' ' . $this->user->metadata->last_name;

        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->setX(1);
        $this->pdf->Write(21, $name);
        
        $this->pdf->Ln(17);     
        $this->pdf->Code39(2, $this->pdf->getY(), $this->user->barcode_id);   //Bar Code

        $this->doPrint($this->location->printer_membership);
    }

    /**
     * Print a valid coupon based on a reward
     * @param Reward $reward
     * The reward model that the coupon is being printed for
     */
    public function printCoupon(Reward $reward)
    {
        $this->init(
            Settings::get('coupon_width'), 
            Settings::get('coupon_height'), 
            Settings::get('coupon_orientation')
        );

        $expires = date( 'm-d-Y', strtotime( "+" . $reward->days_valid ." days" ) );

        $logo = $this->getLogoPath();

        $this->pdf->Image($logo, 8, 1,-170);
        
        $this->pdf->Ln(14);     
        $this->pdf->SetFont('Arial', 'B', 8);
        $this->pdf->setX(8);
        $this->pdf->Write(4, $reward->fine_print);
        
        $this->pdf->Ln(6);      
        $this->pdf->SetFont('Arial', 'B', 8);
        $this->pdf->setY($this->pdf->getY());
        $this->pdf->setX(7);
        $this->pdf->Write(8, "Expires: " . $expires); //Expiration Date
        $this->pdf->Ln(7);      
        $this->pdf->setX(8);
        $this->pdf->Code39(8, $this->pdf->getY(), $reward->barcode);   //Bar Code
        $this->pdf->Ln(8);  
        $this->pdf->setX(8);    
        $this->pdf->SetFont('Arial', 'B', 8);
        $this->pdf->setY($this->pdf->getY()+8);     
        
        $this->pdf->setX(8);
        $this->pdf->Write(8, Lang::get('dma.friends::lang.rewards.couponText'));
        
        $this->pdf->setX(8);
        $this->pdf->Write(14, '      ');

        $this->doPrint($this->location->printer_reward);

    }

    public function getLogoPath()
    {
        $activeTheme = Theme::getActiveTheme();
        $themeDir = $activeTheme->getDirName();
        $logo = base_path() . '/themes/' . $themeDir . $this->logo;

        return $logo;
    }
}
