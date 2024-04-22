{capture name='_smarty_debug' assign=debug_output}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Smarty Debug Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
<style>
            {literal}
    th, td {
                font-family: monospace;
                font-size: 80%;
                list-style: 1;
                padding: 3px;
            }
            {/literal}
        </style>
    </head>
    <body>
<div class="container-sm">

    <h1>Smarty {Smarty::SMARTY_VERSION} Debug Console
        -  {if isset($template_name)}{$template_name|debug_print_var nofilter} {/if}{if !empty($template_data)}Total Time {$execution_time|string_format:"%.5f"}{/if}</h1>

    {if !empty($template_data)}
        <h2>included templates &amp; config files (load time in seconds)</h2>
        <div>
            {foreach $template_data as $template}
                <span style="color: navy;">{$template.name}</span>
                <br>&nbsp;&nbsp;<span class="exectime">
                (compile {$template['compile_time']|string_format:"%.5f"}) (render {$template['render_time']|string_format:"%.5f"}) (cache {$template['cache_time']|string_format:"%.5f"})
                 </span>
                <br>
            {/foreach}
        </div>
    {/if}

    <h2 class="text-primary">assigned template variables</h2>

<div class="d-none d-md-flex justify-content-center">
        <nav class="my-0 pt-1 nav nav-pills flex-row">
{foreach $assigned_vars as $vars}
<a href="#" class="btn btn-outline-primary btn-sm mb-1 me-1 flex-md-fill text-md-center" >${$vars@key}</a>
{/foreach}
                </nav>
</div>

    <table class="table table-striped table-condensed table-bordered" id="table_assigned_vars">
<thead>
    <tr>
        <th>variables</th>
        <th>Value</th>
        <th>Attributes</th>
    </tr>
</thead>
        {foreach $assigned_vars as $vars}
            <tr>
                <td>
                    <label class="text-primary">${$vars@key}</label>
                    {if isset($vars['nocache'])}<sub class="text-muted ms-3">Nocache</sub><br>{/if}
                    {if isset($vars['scope'])}<sub class="text-muted ms-3">Origin:</sub> {$vars['scope']|debug_print_var nofilter}{/if}
                </td>
                <td>
                    {* <small>Value</small> *}
                    {$vars['value']|debug_print_var:10:80 nofilter}
                </td>
                <td>
                    {if isset($vars['attributes'])}
                        {* <small>Attributes</small> *}
                        {$vars['attributes']|debug_print_var nofilter}
                    {/if}
                </td>
            </tr>
         {/foreach}


    </table>

    <h2 style="color: palegreen;">assigned config file variables</h2>

    <table class="table table-striped table-condensed table-bordered" id="table_config_vars">
        {foreach $config_vars as $vars}
            <tr>
                <td>
                    <label style="color: palegreen;">#{$vars@key}#</label>
                    {if isset($vars['scope'])}<strong>Origin:</strong> {$vars['scope']|debug_print_var nofilter}{/if}
                </td>
                <td>
                    {$vars['value']|debug_print_var:10:80 nofilter}
                </td>
            </tr>
        {/foreach}

    </table>
</div>
    </body>
    </html>
{/capture}
<script type="text/javascript">
    {$id = '__Smarty__'}
    {if $display_mode}{$id = "$offset$template_name"|md5}{/if}
    _smarty_console = window.open("antaNT64", "console{$id}", "width=80%, height=80%, resizable, scrollbars=yes");
    _smarty_console.document.write("{$debug_output|escape:'javascript' nofilter}");
    _smarty_console.document.close();
</script>
