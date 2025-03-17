<?php

it('will not use debugging functions')
    ->expect(['die', 'dd', 'dump', 'ray', 'ddRawSql', 'var_dump'])
    ->each->not->toBeUsed();
