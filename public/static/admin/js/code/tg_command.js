define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'code.tg_command/index',
        add_url: 'code.tg_command/add',
        edit_url: 'code.tg_command/edit',
        delete_url: 'code.tg_command/delete',
        export_url: 'code.tg_command/export',
        modify_url: 'code.tg_command/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: "checkbox"},
                    {field: 'id', width: 80, title: 'ID'},
                    {field: 'command', minWidth: 80, title: '命令'},
                    {field: 'description', minWidth: 80, title: '描述'},
                    {field: 'call_controller', minWidth: 80, title: '命令指向控制器'},
                    {field: 'call_action', minWidth: 80, title: '命令指向方法'},
                    {field: 'create_time', minWidth: 80, title: '创建时间', search: 'range'},
                    {width: 250, title: '操作', templet: ea.table.tool}
                ]],
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
    };
    return Controller;
});