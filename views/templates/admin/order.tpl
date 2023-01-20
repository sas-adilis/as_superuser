<div class="card d-print-none">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h3 class="card-header-title">
                    {l s='Connexion' mod='as_superuser'}
                </h3>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form name="order_message" method="post" action="" class="form-horizontal">
            {if $is_logged}
                <div class="alert alert-info">
                    {l s='You are already logged in as %customer%' sprintf=['%customer%' => "`$customer->firstname` `$customer->lastname`"] mod='as_superuser'}
                </div>
                <p>
                    {l s="Open" mod='as_superuser'} :
                    <a href="{$link->getPageLink('cart', null, null, ['action' => 'show'])}" target="_blank">{l s="Cart" mod='as_superuser'}</a> -
                    <a href="{$link->getPageLink('index')}" target="_blank">{l s="Home" mod='as_superuser'}</a></p>
                <div class="text-left">
                    <button type="submit" class="btn btn-primary" name="submitSuperUserLogout">{l s="Logout" mod='as_superuser'}</button>
                </div>
            {else}
                <input type="hidden" name="id_customer" value="{$customer->id|intval}">
                <div class="text-left">
                    <button type="submit" class="btn btn-primary" name="submitSuperUser">{l s="Connect as %customer%" sprintf=['%customer%' => "`$customer->firstname` `$customer->lastname`"] mod='as_superuser'}</button>
                </div>
            {/if}
        </form>
    </div>
</div>