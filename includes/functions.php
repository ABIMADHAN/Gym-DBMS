<?php

function isCustomer() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer';
}

function isTrainer() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'trainer';
}
