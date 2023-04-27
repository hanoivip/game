<?php 

if (! function_exists('wizard_roles')) 
{
    function wizard_roles($nextRoute)
    {
        return view('hanoivip::wizard-role', ['next' => $nextRoute]);
    }
}
