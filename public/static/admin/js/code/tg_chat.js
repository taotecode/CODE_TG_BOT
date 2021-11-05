define(["jquery", "easy-admin"], function ($, ea) {

    var laymd = layui.laymd;

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'code.tg_chat/index',
        add_url: 'code.tg_chat/add',
        edit_url: 'code.tg_chat/edit',
        delete_url: 'code.tg_chat/delete',
        export_url: 'code.tg_chat/export',
        modify_url: 'code.tg_chat/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar: ['refresh','delete', 'export'],
                cols: [[
                    {type: "checkbox"},
                    {field: 'id', width: 80, title: 'ID'},
                    {field: 'chat_id', minWidth: 80, title: '聊天方式ID'},
                    {field: 'chat_username', minWidth: 80, title: '聊天用户名'},
                    {field: 'from_username', minWidth: 80, title: '发送者用户名'},
                    {field: 'text', minWidth: 80, title: '消息内容'},
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