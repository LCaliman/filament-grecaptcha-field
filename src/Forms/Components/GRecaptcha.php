<?php

namespace AbanoubNassem\FilamentGRecaptchaField\Forms\Components;

use Filament\Forms\Components\Field;


class GRecaptcha extends Field
{
    protected string $view = 'filament-grecaptcha-field::forms.components.g-recaptcha';


    public function setUp(): void
    {
        parent::setUp();
        $this->rules('required|captcha');
        $this->dehydrated(false);
        $this->label('');
    }

    public function callBeforeStateDehydrated(&$state = []): static
    {
        parent::callBeforeStateDehydrated($state);

        if (method_exists($this->getLivewire(), 'dispatchFormEvent')) {
            $this->getLivewire()->dispatchFormEvent('resetCaptcha');
        } else if (method_exists($this->getLivewire(), 'emit')) {
            $this->getLivewire()->emit('resetCaptcha');
        } else {
            $this->getLivewire()->dispatch('resetCaptcha');
        }

        return $this;
    }
}
