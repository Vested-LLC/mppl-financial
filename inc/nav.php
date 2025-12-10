<div id="nav" role="navigation">
<div class="nav-inner">
        <button id="nav-close" aria-controls="nav" aria-label="Close Main Navigation">Close</button>  
        <!-- Site logo -->
        <nav class="main-nav" aria-label="Main Navigation">
            <?php wp_nav_menu([
                'menu' => 'Main Menu'
            ]); ?>
        </nav>
        <div id="nav-btns">
            <a href="/schedule-a-call" class="btn">Start a Conversation</a>
            <a href="/client-login" class="btn">Client Login</a>
        </div>
    </div>
</div>
