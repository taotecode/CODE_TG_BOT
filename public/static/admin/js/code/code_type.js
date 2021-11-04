define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'code.code_type/index',
        add_url: 'code.code_type/add',
        edit_url: 'code.code_type/edit',
        delete_url: 'code.code_type/delete',
        export_url: 'code.code_type/export',
        modify_url: 'code.code_type/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: "checkbox"},
                    {field: 'id', width: 80, title: 'ID'},
                    {field: 'SystemCommand.command', minWidth: 80, title: '关联命令'},
                    {field: 'storage_time', minWidth: 80, title: '存储时间'},
                    {field: 'code_time', minWidth: 80, title: 'code存储时间'},
                    {field: 'number', minWidth: 80, title: '每天投放次数'},
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