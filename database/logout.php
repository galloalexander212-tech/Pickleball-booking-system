<?php
require "function.php";

session_unset();
session_destroy();

redirect("../index.php");