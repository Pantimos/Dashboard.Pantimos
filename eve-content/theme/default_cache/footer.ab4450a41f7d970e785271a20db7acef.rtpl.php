<?php if(!class_exists('raintpl')){exit;}?>            <div class="mastfoot">
                <div class="inner">
                    <p>Code BY <a href="http://soulteary.com" target="_blank">@soulteary</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script><?php echo $PAGE_SCRIPT;?></script>
<?php if( DEBUG==true ){ ?>

<div id="debug-box">
<?php echo var_dump( $DEBUG_PAGE_ARGU );?>

<?php echo var_dump( $DEBUG_DATA );?>

<?php echo $DEBUG_MARKDOWN;?>

<?php echo $DEBUG_IP_CURRENT;?>

<?php echo $DEBUG_TIMESTAMP;?>

</div>
<?php } ?>

    </body>
</html>