<?php

namespace app\modules\api\presentators;

use app\modules\faq\presentators\FaqPresentator;
use app\modules\menu\readModels\MenuItemReader;
use app\modules\page\models\Page;
use app\modules\portfolio\presentators\PortfolioPresentator;
use app\modules\review\presentators\ReviewPresentator;
use app\modules\setting\components\Settings;
use app\modules\slider\presentator\SliderPresentator;

class MainPresentator
{
    private $menuItems;
    private $sliderPresentator;
    private $reviewPresentator;
    private $portfolioPresentator;
    private $faqPresentator;

    public function __construct(
        MenuItemReader $menuItems,
        SliderPresentator $sliderPresentator,
        ReviewPresentator $reviewPresentator,
        PortfolioPresentator $portfolioPresentator,
        FaqPresentator $faqPresentator
    )
    {
        $this->menuItems = $menuItems;
        $this->sliderPresentator = $sliderPresentator;
        $this->reviewPresentator = $reviewPresentator;
        $this->portfolioPresentator = $portfolioPresentator;
        $this->faqPresentator = $faqPresentator;
    }


    public function getIndex()
    {
        $page = Page::getOrCreate('index');

        return [
            'meta' => $page->getMetaTags(),
            'slider' => $this->sliderPresentator->getSlides(),
        ];
    }

    public function getLayout()
    {
        return [
            'header' => [
                'phone' => Settings::getArray('phone'),
            ],
            'footer' => [
                'contacts' => [
                    'phone' => Settings::getArray('phone'),
                    'email' => Settings::getArray('email'),
                    'skype' => Settings::getArray('skype'),
                    'viber' => Settings::getArray('viber'),
                    'whatsapp' => Settings::getArray('whatsapp'),
                ],
                'socials' => [
                    'facebook' => Settings::getValue('facebook'),
                    'telegram' => Settings::getValue('telegram'),
                    'instagram' => Settings::getValue('instagram'),
                ],
                'copyright' => Settings::getValue('copyright'),
            ],
        ];
    }

    public function getHome()
    {
        $page = Page::getOrCreate('index');

        return [
            'meta' => $page->getMetaTags(),
            'about' => [
                'image' => Settings::getValue('about_image'),
                'text' => Settings::getValue('about'),
            ],
            'portfolio' => $this->portfolioPresentator->getPreviewPortfolio(),
            'advantages' => [
                'description' => Settings::getValue('advantages')
            ],
            'reveiws' => $this->reviewPresentator->getPreviewReviews(),
            'faq' => $this->faqPresentator->getPreviewFaqs(),
            'contacts' => [
                'phone' => Settings::getArray('phone'),
                'email' => Settings::getArray('email'),
                'skype' => Settings::getArray('skype'),
                'viber' => Settings::getArray('viber'),
                'whatsapp' => Settings::getArray('whatsapp'),
            ],
            'map' => Settings::getValue('map')
        ];
    }

    public function getContacts()
    {
        return [
            'company_name' => Settings::getValue('company_name'),
            'contacts' => [
                'phone' => Settings::getArray('phone'),
                'email' => Settings::getArray('email'),
                'skype' => Settings::getArray('skype'),
                'viber' => Settings::getArray('viber'),
                'whatsapp' => Settings::getArray('whatsapp'),
            ],
            'socials' => [
                'facebook' => Settings::getValue('facebook'),
                'telegram' => Settings::getValue('telegram'),
                'instagram' => Settings::getValue('instagram'),
            ],
            'operator_working_hours' => Settings::getValue('operator_working_hours'),
            'office_working_hours' => Settings::getValue('office_working_hours'),
            'denomination' => Settings::getValue('denomination'),
            'tin' => Settings::getValue('tin'),
            'checkpoint' => Settings::getValue('checkpoint'),
            'psrn' => Settings::getValue('psrn'),
            'rcbo' => Settings::getValue('rcbo'),
            'legal_address' => Settings::getValue('legal_address'),

            'map' => Settings::getValue('map')
        ];
    }
}