<?php

namespace App\Library;

class Api_Quoted
{
    public $userTest;
    public $sysTest;

    public function __construct($userTset,$sysTest)
    {
        $this->userTest = $userTset;
        $this->sysTest = $sysTest;
    }

    public function reckon()
    {
        foreach ($this->userTest->auto as $k=>$v)
        {
            $rename = $v->rename;
            dd($this->sysTest->auto);
            $status = $v->status;
            $suanfa = $this->sysTest->auto->$rename->$status->suanfa;
            $value = $this->sysTest->auto->$rename->$status->value;

        }
    }
}
