<div class="col-12" id="layout"></div>
<script>
    (async function () {
        await builder.Storage._ensureReady?.();
        $(document).ready(function(){
            $.ajax({
                url: '/api/roles/fetch?id=<?= $this->Request->getParams('GET', 'id') ?>',
                type: 'GET',dataType: 'json',
                error: function(xhr, status, error) {
                    let color = 'info', icon = 'question-circle', title = builder.Locale.get(xhr.statusText), content = builder.Locale.get(xhr.responseText);
                    switch(xhr.status){
                        case 403: color = 'danger'; icon = 'shield-lock'; break;
                        case 404: color = 'warning'; icon = 'question-diamond'; break;
                        case 500: color = 'danger'; icon = 'bug'; break;
                    }
                    builder.Component("alert","#layout",{icon:icon,color:color,title:title},function(alert,component){component.content.html('<pre class="m-0 p-2">'+content+'</pre>');});
                },
                success: async function(response) {

                    // Configure Storage
                    builder.Storage.setKey(`role:${response.record.id}`);
                    await builder.Storage.set(response);
                    console.log(await builder.Storage.get());

                    // Set the color, icon and label
                    var color = ['secondary','primary','success','warning','danger'];
                    var icon = ['ban','eye','plus-lg','pencil','trash'];
                    var label = ['None','Read','Create','Update','Delete'];

                    // Set the element
                    var element = $('#layout');

                    // Setup the layout
                    element.row = $(document.createElement('div')).addClass('row').appendTo(element);
                    element.col1 = $(document.createElement('div')).addClass('col-12 col-md-6 col-lg-4').appendTo(element.row);
                    element.col2 = $(document.createElement('div')).addClass('col-12 col-md-6 col-lg-8').appendTo(element.row);

                    // Create the Details Card
                    const Details = builder.Component(
                        "card",
                        element.col1,
                        {
                            icon: "shield-lock",
                            title: builder.Locale.get('Details'),
                        },
                        async function(card,component){

                            // Retrieve the record
                            let record = await builder.Storage.get('record');

                            // Styling
                            component.body.addClass('d-flex flex-column justify-content-center align-items-center').attr({
                                "title": record.description,
                                "data-bs-title": record.description,
                                "data-bs-toggle": "tooltip",
                                "data-bs-placement": "bottom",
                            });
                            new bootstrap.Tooltip(component.body);

                            // Insert the role's icon
                            component.body.icon = $(document.createElement('div')).addClass('rounded-circle border border-3 border-light d-flex justify-content-center align-items-center position-relative').css({"height": "256px", "width": "256px"}).appendTo(component.body);
                            component.body.icon.img = $(document.createElement('i')).attr({
                                "class": "bi bi-shield-lock",
                                "style": "font-size: 176px;",
                            }).appendTo(component.body.icon);

                            // Insert the role's name
                            component.body.name = $(document.createElement('div')).addClass('position-relative mt-2 text-center').appendTo(component.body);
                            component.body.name.string = $(document.createElement('h2')).attr({
                                "class": "fw-lighter m-0 cursor-default",
                            }).text(record.name).appendTo(component.body.name);
                            if(record.isDefault){
                                component.body.name.default = $(document.createElement('span')).attr({
                                    "class": "badge text-bg-success fs-6 mt-1",
                                }).text(builder.Locale.get('Default')).appendTo(component.body.name);
                            }
                            component.body.name.btn = $(document.createElement('button')).attr({
                                "type": "button",
                                "class": "btn btn-sm btn-warning fs-5 rounded-circle position-absolute",
                                "style": "transition: all 0.5s ease-in-out; height: 48px!important; width: 48px!important; top: calc(50% - 24px); right: -56px;",
                            }).html('<i class="bi bi-pencil"></i>').appendTo(component.body.name);
                            component.body.name.btn.click(function(){
                                builder.Component(
                                    "modal",
                                    {
                                        onEnter: false,
                                        destroy: true,
                                        icon: "pencil",
                                        title: builder.Locale.get('Edit Role'),
                                        cancel: false,
                                        submit: true,
                                        callback: {
                                            submit: function(element,modal){
                                                element.form.submit();
                                            },
                                        },
                                    },
                                    function(modal,component){
                                        const componentModal = component;
                                        component.addClass('modal-warning');
                                        component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                            "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                        });
                                        component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-save me-1').prependTo(component.footer.submit);
                                        component.form = builder.Component(
                                            'form',
                                            component.body,
                                            {
                                                class:{
                                                    form: 'row row-cols-3',
                                                    field: 'col',
                                                },
                                                callback:{
                                                    submit: function(form){

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: form.val(),
                                                            success: function(response) {

                                                                // Hide the modal
                                                                modal.hide();
                                                            }
                                                        });
                                                    },
                                                },
                                            },
                                            function(form,component){

                                                // name
                                                form.add(
                                                    {
                                                        name: 'name',
                                                        label: builder.Locale.get('Name'),
                                                        icon: 'hash',
                                                        type: 'text',
                                                        value: record.name,
                                                        class: {
                                                            field: 'col-12 mb-3',
                                                        },
                                                    }
                                                );

                                                // description
                                                form.add(
                                                    {
                                                        name: 'description',
                                                        label: builder.Locale.get('Description'),
                                                        icon: 'hash',
                                                        type: 'textarea',
                                                        value: record.description,
                                                        class: {
                                                            field: 'col-12 mb-3',
                                                        },
                                                    },
                                                    function(input,form){
                                                        input.input.addClass('min-vh-20');
                                                    },
                                                );

                                                // isDefault
                                                form.add(
                                                    {
                                                        name: 'isDefault',
                                                        label: builder.Locale.get('Set as Default'),
                                                        icon: 'hash',
                                                        type: 'switch',
                                                        value: record.isDefault,
                                                        class: {
                                                            field: 'col-12',
                                                        },
                                                    },
                                                );

                                                // Show the modal
                                                modal.show();
                                            },
                                        );
                                    },
                                );
                            });
                        },
                    );

                    // Create a Tabs component
                    const Tabs = builder.Component(
                        "tabs",
                        element.col2,
                        {
                            class: {
                                navbar: 'nav-pills',
                            },
                        },
                        async function(tabs,card){

                            // Retrieve the record
                            let record = await builder.Storage.get('record');

                            // Set the table
                            let table = 'roles'

                            // Styling
                            card._component.body.removeClass('card-body');

                            // Permissions
                            tabs.add(
                                'permissions',
                                {
                                    icon: "shield-check",
                                    label: builder.Locale.get("Permissions"),
                                },
                                async function(tab,nav){

                                    builder.Component(
                                        "table",
                                        tab,
                                        {
                                            class: {
                                                buttons: "px-4 pt-4",
                                                table: "border-top",
                                                footer: "px-4 pt-2 pb-4",
                                            },
                                            showButtonsLabel: false,
                                            selectTools:false,
                                            actions:{
                                                none:{
                                                    label:builder.Locale.get('None'),
                                                    icon:'ban',
                                                    class: {
                                                        item: "text-bg-secondary",
                                                    },
                                                    action:function(event, table, dt, node, row, data){

                                                        // Update the table
                                                        data.level = 0;

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            permissions[record.permission] = record.level;
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.update(row,data);
                                                            }
                                                        });
                                                    },
                                                },
                                                read:{
                                                    label:builder.Locale.get('Read'),
                                                    icon:'eye',
                                                    class: {
                                                        item: "text-bg-primary",
                                                    },
                                                    action:function(event, table, dt, node, row, data){

                                                        // Update the table
                                                        data.level = 1;

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            permissions[record.permission] = record.level;
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.update(row,data);
                                                            }
                                                        });
                                                    },
                                                },
                                                create:{
                                                    label:builder.Locale.get('Create'),
                                                    icon:'plus-lg',
                                                    class: {
                                                        item: "text-bg-success",
                                                    },
                                                    action:function(event, table, dt, node, row, data){

                                                        // Update the table
                                                        data.level = 2;

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            permissions[record.permission] = record.level;
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.update(row,data);
                                                            }
                                                        });
                                                    },
                                                },
                                                update:{
                                                    label:builder.Locale.get('Update'),
                                                    icon:'pencil',
                                                    class: {
                                                        item: "text-bg-warning",
                                                    },
                                                    action:function(event, table, dt, node, row, data){

                                                        // Update the table
                                                        data.level = 3;

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            permissions[record.permission] = record.level;
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.update(row,data);
                                                            }
                                                        });
                                                    },
                                                },
                                                delete:{
                                                    label:builder.Locale.get('Delete'),
                                                    icon:'trash',
                                                    class: {
                                                        item: "text-bg-danger",
                                                    },
                                                    action:function(event, table, dt, node, row, data){

                                                        // Update the table
                                                        data.level = 4;

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            permissions[record.permission] = record.level;
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.update(row,data);
                                                            }
                                                        });
                                                    },
                                                },
                                                remove:{
                                                    label:builder.Locale.get('Remove'),
                                                    icon:'trash',
                                                    action:function(event, table, dt, node, row, data){

                                                        // Retrieve the permissions
                                                        var permissions = {};
                                                        for(const [key, record] of Object.entries(table.data())){
                                                            if(data.permission !== record.permission){
                                                                permissions[record.permission] = record.level;
                                                            }
                                                        }

                                                        // Set the AJAX DATA
                                                        var ajaxData = {
                                                            permissions: permissions,
                                                        };

                                                        // AJAX Request
                                                        $.ajax({
                                                            url: '/api/roles/update?id='+record.id,
                                                            headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                            type: 'POST',dataType: 'json',
                                                            data: ajaxData,
                                                            success: function(response) {

                                                                // Update the table
                                                                table.delete(row);
                                                            }
                                                        });
                                                    },
                                                },
                                            },
                                            datatable:{
                                                columnDefs:[
                                                    { target: 0, visible: true, responsivePriority: 1, title: builder.Locale.get('Permission'), name: 'permission', data: 'permission' },
                                                    { target: 1, visible: true, responsivePriority: 2, title: builder.Locale.get('Level'), name: 'level', data: 'level', render: function(data, type, row) {
                                                        return '<h5><span class="badge text-bg-'+color[data]+'"><i class="me-1 bi bi-'+icon[data]+'"></i>'+builder.Locale.get(label[data])+'</span></h5>';
                                                    }},
                                                ],
                                                buttons:[
                                                    {
                                                        className : 'btn-success',
                                                        init: function (dt, node){
                                                            $(node).removeClass('btn-secondary');
                                                        },
                                                        text: '<i class="bi bi-plus-lg me-2"></i>'+builder.Locale.get('Add Permission'),
                                                        action:async function(event, dt, node, config){

                                                            // Retrieve the current permissions
                                                            var permissions = {};
                                                            for(const [key, record] of Object.entries(dt.data().toArray())){
                                                                permissions[record.permission] = record.level;
                                                            }

                                                            // Build options
                                                            var options = [];
                                                            for(const [key, record] of Object.entries(await builder.Storage.get('dependencies:permissions') ?? {})){
                                                                if(permissions[record] === undefined){
                                                                    options.push({id: record, text: record});
                                                                }
                                                            }

                                                            // Build levels
                                                            var levels = [
                                                                {id: 0, text: builder.Locale.get(label[0])},
                                                                {id: 1, text: builder.Locale.get(label[1])},
                                                                {id: 2, text: builder.Locale.get(label[2])},
                                                                {id: 3, text: builder.Locale.get(label[3])},
                                                                {id: 4, text: builder.Locale.get(label[4])},
                                                            ];

                                                            // Create a modal with a form
                                                            builder.Component(
                                                                "modal",
                                                                {
                                                                    onEnter: false,
                                                                    destroy: true,
                                                                    icon: "plus-lg",
                                                                    title: builder.Locale.get('Add Permission'),
                                                                    cancel: false,
                                                                    submit: true,
                                                                    callback: {
                                                                        submit: function(element,modal){
                                                                            element.form.submit();
                                                                        },
                                                                    },
                                                                },
                                                                function(modal,component){
                                                                    const componentModal = component;
                                                                    component.addClass('modal-success');
                                                                    component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                                                        "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                                                    }).text(builder.Locale.get('Add'));
                                                                    component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-plus-lg me-1').prependTo(component.footer.submit);
                                                                    component.form = builder.Component(
                                                                        'form',
                                                                        component.body,
                                                                        {
                                                                            class:{
                                                                                form: 'row row-cols-3',
                                                                                field: 'col',
                                                                            },
                                                                            callback:{
                                                                                val: function(values){
                                                                                    values.level = parseInt(values.level);
                                                                                    return values;
                                                                                },
                                                                                submit: function(form){

                                                                                    // Add the record to the table
                                                                                    dt.row.add(form.val()).draw();

                                                                                    // Retrieve the current permissions
                                                                                    var rights = {};
                                                                                    for(const [key, record] of Object.entries(dt.data().toArray())){
                                                                                        rights[record.permission] = record.level;
                                                                                    }

                                                                                    // Set the AJAX DATA
                                                                                    var ajaxData = {
                                                                                        permissions: rights,
                                                                                    };

                                                                                    // AJAX Request
                                                                                    $.ajax({
                                                                                        url: '/api/roles/update?id='+record.id,
                                                                                        headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                                                        type: 'POST',dataType: 'json',
                                                                                        data: ajaxData,
                                                                                        success: function(response) {

                                                                                            // Hide the modal
                                                                                            modal.hide();
                                                                                        }
                                                                                    });
                                                                                },
                                                                            },
                                                                        },
                                                                        function(form,component){

                                                                            // permission
                                                                            form.add(
                                                                                {
                                                                                    name: 'permission',
                                                                                    label: builder.Locale.get('Permission'),
                                                                                    icon: 'shield-check',
                                                                                    type: 'select2',
                                                                                    options: options,
                                                                                    modal: componentModal,
                                                                                    allowNew: true,
                                                                                    class: {
                                                                                        field: 'col-12 mb-3',
                                                                                    },
                                                                                }
                                                                            );

                                                                            // level
                                                                            form.add(
                                                                                {
                                                                                    name: 'level',
                                                                                    label: builder.Locale.get('Level'),
                                                                                    icon: 'bar-chart-fill',
                                                                                    type: 'select2',
                                                                                    options: levels,
                                                                                    modal: componentModal,
                                                                                    class: {
                                                                                        field: 'col-12',
                                                                                    },
                                                                                    callback: {
                                                                                        format: function(option, component){

                                                                                            // Check if the option is a placeholder
                                                                                            if (!option.id) { return option.text; }

                                                                                            // Create the option
                                                                                            var option = $(
                                                                                                '<div class="text-bg-'+color[option.id]+'" style="margin: -6px -12px;padding: 6px 12px"><i class="me-2 bi bi-'+icon[option.id]+'"></i>'+builder.Locale.get(label[option.id])+'</div>'
                                                                                            );

                                                                                            // Return the option
                                                                                            return option;
                                                                                        },
                                                                                    }
                                                                                },
                                                                            );

                                                                            // Show the modal
                                                                            modal.show();
                                                                        },
                                                                    );
                                                                },
                                                            );
                                                        },
                                                    }
                                                ],
                                            },
                                        },
                                        async function(table,component){
                                            for(const [permission, level] of Object.entries(await builder.Storage.get('record:permissions') ?? {})){
                                                table.add({permission: permission, level: level});
                                            }
                                        },
                                    );
                                },
                            );

                            // Groups
                            tabs.add(
                                'groups',
                                {
                                    icon: "people",
                                    label: builder.Locale.get("Groups"),
                                },
                                async function(tab,nav){

                                    // Retrieve the groups
                                    let groups = await builder.Storage.get('dependencies:groups');

                                    var actions = {
                                        details:{
                                            label:'Details',
                                            icon:'eye',
                                            action:function(event, table, dt, node, row, data){
                                                window.location.href = "/plugin/groups/details?id=" + data.id + "&name=" + data.name;
                                            }
                                        },
                                        remove:{
                                            label:'Remove',
                                            icon:'trash',
                                            action:function(event, table, dt, node, row, data){

                                                // Retrieve the groups
                                                var groups = [];
                                                for(const [key, record] of Object.entries(table.data())){
                                                    if(data.id !== record.id){
                                                        groups.push(record.id);
                                                    }
                                                }

                                                // Set the AJAX DATA
                                                var ajaxData = {
                                                    groups: JSON.stringify(groups),
                                                };

                                                // AJAX Request
                                                $.ajax({
                                                    url: '/api/roles/update?id='+record.id,
                                                    headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                    type: 'POST',dataType: 'json',
                                                    data: ajaxData,
                                                    success: function(response) {

                                                        // Update the table
                                                        table.delete(row);
                                                    }
                                                });
                                            },
                                        },
                                    }
                                    builder.Component(
                                        "table",
                                        tab,
                                        {
                                            class: {
                                                buttons: "px-4 pt-4",
                                                table: "border-top",
                                                footer: "px-4 pt-2 pb-4",
                                            },
                                            showButtonsLabel: false,
                                            selectTools:false,
                                            actions: actions,
                                            dblclick:function(event, table, dt, node, data){
                                                actions.details.action(event, table, dt, node, null, data);
                                            },
                                            datatable:{
                                                columnDefs:[
                                                    { target: 0, visible: false, responsivePriority: 1000, title: builder.Locale.get('ID'), name: 'id', data: 'id' },
                                                    { target: 1, visible: true, responsivePriority: 1, title: builder.Locale.get('Name'), name: 'name', data: 'name' },
                                                    { target: 2, visible: true, responsivePriority: 2, title: builder.Locale.get('Description'), name: 'description', data: 'description' },
                                                ],
                                                buttons:[
                                                    {
                                                        className : 'btn-success',
                                                        init: function (dt, node){
                                                            $(node).removeClass('btn-secondary');
                                                        },
                                                        text: '<i class="bi bi-plus-lg me-2"></i>'+builder.Locale.get('Add Group'),
                                                        action:function(event, dt, node, config){

                                                            // AJAX Request
                                                            $.ajax({
                                                                url: '/api/groups/fetchAll',
                                                                headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                                type: 'POST',dataType: 'json',
                                                                data: {
                                                                    conditions: [
                                                                        {key: 'isArchived', operator: '<>', value: 1},
                                                                    ]
                                                                },
                                                                success: function(response) {

                                                                    // Retrieve existing members
                                                                    var members = []
                                                                    for(const [key, record] of Object.entries(dt.data().toArray())){
                                                                        members.push(record.id);
                                                                    }

                                                                    // Build options
                                                                    var options = [];
                                                                    for(const [key, group] of Object.entries(response.records)){
                                                                        if($.inArray(group.id, members) === -1){
                                                                            options.push({id: group.id, text: group.name+' - '+group.description});
                                                                        }
                                                                    }

                                                                    // Create a modal with a form
                                                                    builder.Component(
                                                                        "modal",
                                                                        {
                                                                            onEnter: false,
                                                                            destroy: true,
                                                                            icon: "plus-lg",
                                                                            title: builder.Locale.get('Add Group'),
                                                                            cancel: false,
                                                                            submit: true,
                                                                            callback: {
                                                                                submit: function(element,modal){
                                                                                    element.form.submit();
                                                                                },
                                                                            },
                                                                        },
                                                                        function(modal,component){
                                                                            const componentModal = component;
                                                                            component.addClass('modal-success');
                                                                            component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                                                                "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                                                            }).text(builder.Locale.get('Add'));
                                                                            component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-plus-lg me-1').prependTo(component.footer.submit);
                                                                            component.form = builder.Component(
                                                                                'form',
                                                                                component.body,
                                                                                {
                                                                                    class:{
                                                                                        form: 'row row-cols-3',
                                                                                        field: 'col',
                                                                                    },
                                                                                    callback:{
                                                                                        val: function(values){
                                                                                            return parseInt(values.group);
                                                                                        },
                                                                                        submit: function(form){

                                                                                            // Add the record to the table
                                                                                            dt.row.add(response.records[form.val()]).draw();

                                                                                            // Add the group to the list of members
                                                                                            members.push(form.val());

                                                                                            // AJAX Request
                                                                                            $.ajax({
                                                                                                url: '/api/roles/update?id='+record.id,
                                                                                                headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                                                                type: 'POST',dataType: 'json',
                                                                                                data: {groups: members},
                                                                                                success: function(response) {

                                                                                                    // Hide the modal
                                                                                                    modal.hide();
                                                                                                }
                                                                                            });
                                                                                        },
                                                                                    },
                                                                                },
                                                                                function(form,component){

                                                                                    // group
                                                                                    form.add(
                                                                                        {
                                                                                            name: 'group',
                                                                                            label: builder.Locale.get('Group'),
                                                                                            icon: 'people',
                                                                                            type: 'select2',
                                                                                            options: options,
                                                                                            modal: componentModal,
                                                                                            class: {
                                                                                                field: 'col-12',
                                                                                            },
                                                                                        }
                                                                                    );

                                                                                    // Show the modal
                                                                                    modal.show();
                                                                                },
                                                                            );
                                                                        },
                                                                    );
                                                                }
                                                            });
                                                        },
                                                    }
                                                ],
                                            },
                                        },
                                        function(table,component){
                                            for(const [key, group] of Object.entries(groups ?? {})){
                                                table.add(group);
                                            }
                                        },
                                    );
                                },
                            );

                            // Users
                            tabs.add(
                                'users',
                                {
                                    icon: "person",
                                    label: builder.Locale.get("Users"),
                                },
                                async function(tab,nav){

                                    // Retrieve the users
                                    let users = await builder.Storage.get('dependencies:users');

                                    var actions = {
                                        details:{
                                            label:'Details',
                                            icon:'eye',
                                            action:function(event, table, dt, node, row, data){
                                                window.location.href = "/plugin/users/details?id=" + data.id + "&name=" + data.username;
                                            }
                                        },
                                        remove:{
                                            label:'Remove',
                                            icon:'trash',
                                            action:function(event, table, dt, node, row, data){

                                                // Retrieve the users
                                                var users = [];
                                                for(const [key, record] of Object.entries(table.data())){
                                                    if(data.id !== record.id){
                                                        users.push(record.id);
                                                    }
                                                }

                                                // AJAX Request
                                                $.ajax({
                                                    url: '/api/roles/update?id='+record.id,
                                                    headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                    type: 'POST',dataType: 'json',
                                                    data: {users: JSON.stringify(users)},
                                                    success: function(response) {

                                                        // Update the table
                                                        table.delete(row);
                                                    }
                                                });
                                            },
                                        },
                                    }
                                    builder.Component(
                                        "table",
                                        tab,
                                        {
                                            class: {
                                                buttons: "px-4 pt-4",
                                                table: "border-top",
                                                footer: "px-4 pt-2 pb-4",
                                            },
                                            showButtonsLabel: false,
                                            selectTools:false,
                                            actions: actions,
                                            dblclick:function(event, table, dt, node, data){
                                                actions.details.action(event, table, dt, node, null, data);
                                            },
                                            datatable:{
                                                columnDefs:[
                                                    { target: 0, visible: false, responsivePriority: 1000, title: builder.Locale.get('ID'), name: 'id', data: 'id' },
                                                    { target: 1, visible: true, responsivePriority: 1, title: builder.Locale.get('Username'), name: 'username', data: 'username' },
                                                ],
                                                buttons:[
                                                    {
                                                        className : 'btn-success',
                                                        init: function (dt, node){
                                                            $(node).removeClass('btn-secondary');
                                                        },
                                                        text: '<i class="bi bi-plus-lg me-2"></i>'+builder.Locale.get('Add User'),
                                                        action:function(event, dt, node, config){

                                                            // AJAX Request
                                                            $.ajax({
                                                                url: '/api/auth/users',
                                                                type: 'GET',dataType: 'json',
                                                                success: function(response) {

                                                                    // Retrieve existing members
                                                                    var members = []
                                                                    for(const [key, record] of Object.entries(dt.data().toArray())){
                                                                        members.push(record.id);
                                                                    }

                                                                    // Build options
                                                                    var options = [];
                                                                    for(const [key, user] of Object.entries(response.records)){
                                                                        if($.inArray(user.id, members) === -1){
                                                                            options.push({id: user.id, text: user.username+' - '+user.vcard.name});
                                                                        }
                                                                    }

                                                                    // Create a modal with a form
                                                                    builder.Component(
                                                                        "modal",
                                                                        {
                                                                            onEnter: false,
                                                                            destroy: true,
                                                                            icon: "plus-lg",
                                                                            title: builder.Locale.get('Add User'),
                                                                            cancel: false,
                                                                            submit: true,
                                                                            callback: {
                                                                                submit: function(element,modal){
                                                                                    element.form.submit();
                                                                                },
                                                                            },
                                                                        },
                                                                        function(modal,component){
                                                                            const componentModal = component;
                                                                            component.addClass('modal-success');
                                                                            component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                                                                "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                                                            }).text(builder.Locale.get('Add'));
                                                                            component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-plus-lg me-1').prependTo(component.footer.submit);
                                                                            component.form = builder.Component(
                                                                                'form',
                                                                                component.body,
                                                                                {
                                                                                    class:{
                                                                                        form: 'row row-cols-3',
                                                                                        field: 'col',
                                                                                    },
                                                                                    callback:{
                                                                                        val: function(values){
                                                                                            return parseInt(values.user);
                                                                                        },
                                                                                        submit: function(form){

                                                                                            // Add the record to the table
                                                                                            dt.row.add(response.records[form.val()]).draw();

                                                                                            // Add the user to the list of members
                                                                                            members.push(form.val());

                                                                                            // AJAX Request
                                                                                            $.ajax({
                                                                                                url: '/api/roles/update?id='+record.id,
                                                                                                headers: {'X-CSRF-Authorization': CSRF_KEY},
                                                                                                type: 'POST',dataType: 'json',
                                                                                                data: {users: members},
                                                                                                success: function(response) {

                                                                                                    // Hide the modal
                                                                                                    modal.hide();
                                                                                                }
                                                                                            });
                                                                                        },
                                                                                    },
                                                                                },
                                                                                function(form,component){

                                                                                    // user
                                                                                    form.add(
                                                                                        {
                                                                                            name: 'user',
                                                                                            label: builder.Locale.get('User'),
                                                                                            icon: 'people',
                                                                                            type: 'select2',
                                                                                            options: options,
                                                                                            modal: componentModal,
                                                                                            class: {
                                                                                                field: 'col-12',
                                                                                            },
                                                                                        }
                                                                                    );

                                                                                    // Show the modal
                                                                                    modal.show();
                                                                                },
                                                                            );
                                                                        },
                                                                    );
                                                                }
                                                            });
                                                        },
                                                    }
                                                ],
                                            },
                                        },
                                        function(table,component){
                                            for(const [key, user] of Object.entries(users ?? {})){
                                                table.add(user);
                                            }
                                        },
                                    );
                                },
                            );

                            // Notes
                            <?php if($this->Helper->Core->isInstalled('notes')): ?>

                                // Retrieve the notes
                                let notes = await builder.Storage.get('dependencies:notes');

                                // Add the Notes tab
                                tabs.add(
                                    'notes',
                                    {
                                        icon: "stickies",
                                        label: builder.Locale.get("Notes"),
                                    },
                                    function(tab,nav){
                                        card.notes = tab;
                                        NotesFeed(notes ?? [], tab, table, record.id);
                                    },
                                );
                            <?php endif; ?>

                            // Event
                            <?php if($this->Helper->Core->isInstalled('event')): ?>

                                // Retrieve the event
                                let event = await builder.Storage.get('dependencies:event');

                                // Add the Event tab
                                tabs.add(
                                    'activities',
                                    {
                                        icon: "activity",
                                        label: builder.Locale.get("Activity"),
                                    },
                                    function(tab,nav){
                                        tab.addClass('px-4 py-3');
                                        card.activities = tab;
                                        EventFeed(event ?? [], tab);
                                    },
                                );
                            <?php endif; ?>

                            // Relationship
                            <?php if($this->Helper->Core->isInstalled('relationship')): ?>

                                // Retrieve the relationship
                                let relationship = await builder.Storage.get('dependencies:relationship');

                                // Add the Relationship tab
                                tabs.add(
                                    'related',
                                    {
                                        icon: "diagram-2",
                                        label: builder.Locale.get("Related"),
                                    },
                                    function(tab,nav){
                                        tab.addClass('px-4 py-3');
                                        card.related = tab;
                                        RelationshipFeed(relationship, tab, table, record.id, function(feed){
                                            card.related.feed = feed;
                                        });
                                    },
                                );
                            <?php endif; ?>
                        },
                    );
                },
            });
        });
    })();
</script>
