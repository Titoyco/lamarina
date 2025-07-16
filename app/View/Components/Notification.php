<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Notification extends Component
{
    /**
     * Create a new component instance.
     */
    public $message;
    public $show;
    public $textColor;

    public function __construct($message, $show = true, $textColor = 'black')
    {
        $this->message = $message;
        $this->show = $show;
        $this->textColor = $textColor;
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.notification');
    }
}
