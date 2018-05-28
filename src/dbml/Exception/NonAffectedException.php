<?php

namespace ryunosuke\dbml\Exception;

/**
 * INSER/UPDATE/DELETE 操作で作用行が無かった時の例外クラス
 */
class NonAffectedException extends InvalidCountException
{
}
