<?php

declare(strict_types=1);

namespace PHPSTORM_META {
    use Maximaster\Atoa\Contract\Atoa;

    override(Atoa::convertTo('type', 'value'), map(['' => '@']));
}
