<?php

namespace spec\Piper\Sharepoint;

use Piper\Sharepoint\API;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class APISpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(API::class);
    }
}
