<?php
/**
 * __init__
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
require_once '__settings__.php';
error_reporting(E_ALL);

// FIXME!!
date_default_timezone_set('Asia/Tokyo');

Rhaco::import('model.Charge');
Rhaco::import('model.Maintainer');
Rhaco::import('model.Package');
Rhaco::import('model.OpenId');
Rhaco::import('model.Favorite');

Rhaco::constant('HTML_TEMPLATE_ARG_ESCAPE', true);
