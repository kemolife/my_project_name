<?php

namespace SingAppBundle\DBAL;

class EnumPostStatusType extends EnumType
{
    protected $name = 'enum_post_status_type';

    protected $values = array('pending', 'failed', 'posted');
}
