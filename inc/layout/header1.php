<?php

/* THIS HEADER GOES AFTER THE HERO UNTI ON PAGES THAT HAVE ONE */

print'
    <header class="centered-navigation">
        <div class="centered-navigation-wrapper">
            <a href="'.$protocol.'://'.$site.'/" class="mobile-logo">
                <img src="/img/h.png" alt="H logo">
            </a>
            <a href="" class="centered-navigation-menu-button">MENU</a>
            <ul class="centered-navigation-menu">
                <li class="nav-link logo">
                  <a href="'.$protocol.'://'.$site.'/" class="logo">
                    <img src="/img/h.png" alt="H logo">
                  </a>
                </li>
                <li class="nav-link"><a href="'.$protocol.'://'.$site.'/artwork/">Artwork</a></li>
                <li class="nav-link"><a href="'.$protocol.'://'.$site.'/about/">Helmar &amp; Charles</a></li>
                <li class="nav-link"><a href="'.$protocol.'://'.$site.'/contact/">Stay In Touch</a></li>
                <li class="nav-link"><a href="">Blog</a></li>
                <li class="nav-link"><a href="">Store</a></li>
                <li class="nav-link"><a href="'.$protocol.'://'.$site.'/account/login/?redir='.$currentpage.'">Log In</a></li>
            </ul>
        </div>
    </header>
';

?>