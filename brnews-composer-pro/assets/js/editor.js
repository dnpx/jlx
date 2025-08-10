document.addEventListener('DOMContentLoaded', function () {
    const blockLibrary = document.getElementById('brcp-block-library');
    const iframe = document.getElementById('brcp-editor-iframe');
    const dropZone = document.createElement('div');
    dropZone.id = 'brcp-drop-zone';
    iframe.parentNode.insertBefore(dropZone, iframe.nextSibling);

    let currentLayout = { rows: [] };
    let selectedModuleId = null;
    const postId = new URLSearchParams(window.location.search).get('post_id');

    // Fetch initial layout
    wp.apiFetch({ path: `/wp/v2/posts/${postId}?context=edit` }).then(post => {
        if (post.meta._brcp_layout && post.meta._brcp_layout[0]) {
            try {
                currentLayout = JSON.parse(post.meta._brcp_layout[0]);
            } catch (e) {
                console.error('Error parsing layout JSON:', e);
            }
        }
    });

    if (blockLibrary) {
        wp.apiFetch({ path: '/brcp/v1/library' }).then(groups => {
            groups.forEach(group => {
                const groupEl = document.createElement('div');
                groupEl.classList.add('brcp-block-group');

                const groupHeader = document.createElement('h3');
                groupHeader.classList.add('brcp-block-group-header');
                groupHeader.textContent = group.title;
                groupEl.appendChild(groupHeader);

                const groupItems = document.createElement('div');
                groupItems.classList.add('brcp-block-group-items');

                group.items.forEach(item => {
                    const itemEl = document.createElement('div');
                    itemEl.classList.add('brcp-block-item');
                    itemEl.dataset.type = item.type;
                    itemEl.dataset.preset = JSON.stringify(item.preset);
                    itemEl.draggable = true;

                    const icon = document.createElement('span');
                    icon.classList.add('dashicons', item.icon);
                    itemEl.appendChild(icon);

                    const label = document.createElement('span');
                    label.classList.add('brcp-block-item-label');
                    label.textContent = item.label;
                    itemEl.appendChild(label);

                    itemEl.addEventListener('dragstart', (e) => {
                        e.dataTransfer.setData('text/plain', JSON.stringify({
                            type: item.type,
                            preset: item.preset
                        }));
                    });

                    groupItems.appendChild(itemEl);
                });

                groupEl.appendChild(groupItems);
                blockLibrary.appendChild(groupEl);
            });
        });
    }

    // Drag and drop
    let draggedItem = null;

    document.addEventListener('dragstart', (e) => {
        if (e.target.closest('.brcp-block-item')) {
            draggedItem = e.target.closest('.brcp-block-item');
            dropZone.style.display = 'block';
        }
    });

    document.addEventListener('dragend', () => {
        draggedItem = null;
        dropZone.style.display = 'none';
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('brcp-drop-zone--over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('brcp-drop-zone--over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('brcp-drop-zone--over');
        if (draggedItem) {
            const blockPreset = JSON.parse(draggedItem.dataset.preset);
            const blockType = draggedItem.dataset.type;

            const newModule = {
                id: `brcp-module-${Date.now()}`,
                type: blockType,
                props: blockPreset
            };

            // For now, add to the first column of the first row
            if (currentLayout.rows.length === 0) {
                currentLayout.rows.push({
                    columns: [{ width: 12, modules: [] }]
                });
            }
            // Add a new row for each block for simplicity
            currentLayout.rows.push({
                columns: [{ width: 12, modules: [newModule] }]
            });


            saveAndRenderLayout(currentLayout);
        }
    });

    window.addEventListener('message', function (event) {
        if (event.data.action === 'selectBlock') {
            const { blockType, moduleId } = event.data;
            selectedModuleId = moduleId;
            const inspector = document.getElementById('brcp-block-inspector');
            inspector.innerHTML = 'Loading...';

            wp.apiFetch({ path: `/brcp/v1/block/${blockType}` }).then(block => {
                inspector.innerHTML = `<div class="brcp-panel-header"><h3>${block.label}</h3></div>`;

                if (block.fields) {
                    const fieldsWrapper = document.createElement('div');
                    fieldsWrapper.classList.add('brcp-inspector-fields');

                    block.fields.forEach(field => {
                        const fieldWrapper = document.createElement('div');
                        fieldWrapper.classList.add('brcp-inspector-field');

                        const label = document.createElement('label');
                        label.textContent = field.label;
                        label.htmlFor = `brcp-field-${field.key}`;
                        fieldWrapper.appendChild(label);

                        let input;
                        if (field.type === 'select') {
                            input = document.createElement('select');
                            field.options.forEach(option => {
                                const optionEl = document.createElement('option');
                                optionEl.value = option;
                                optionEl.textContent = option;
                                input.appendChild(optionEl);
                            });
                        } else {
                            input = document.createElement('input');
                            input.type = field.type;
                        }
                        input.name = field.key;
                        input.id = `brcp-field-${field.key}`;

                        // Set current value
                        const module = findModule(currentLayout, selectedModuleId);
                        if (module && module.props[field.key]) {
                            input.value = module.props[field.key];
                        }

                        input.addEventListener('input', (e) => {
                            const newProps = { [e.target.name]: e.target.value };
                            const newLayout = findAndUpdateModule(currentLayout, selectedModuleId, newProps);
                            currentLayout = newLayout;
                            saveAndRenderLayout(newLayout);
                        });

                        fieldWrapper.appendChild(input);
                        fieldsWrapper.appendChild(fieldWrapper);
                    });
                    inspector.appendChild(fieldsWrapper);
                }
            });
        }
    });

    function saveAndRenderLayout(layout) {
        wp.apiFetch({
            path: `/brcp/v1/layout/${postId}`,
            method: 'POST',
            data: layout
        }).then(() => {
            iframe.contentWindow.postMessage({
                action: 'renderLayout',
                layout: layout
            }, '*');
        });
    }

    function findModule(layout, moduleId) {
        for (const row of layout.rows) {
            for (const col of row.columns) {
                for (const module of col.modules) {
                    if (module.id === moduleId) {
                        return module;
                    }
                }
            }
        }
        return null;
    }

    function findAndUpdateModule(layout, moduleId, newProps) {
        const newLayout = JSON.parse(JSON.stringify(layout)); // Deep copy
        newLayout.rows.forEach(row => {
            row.columns.forEach(col => {
                col.modules.forEach(module => {
                    if (module.id === moduleId) {
                        module.props = { ...module.props, ...newProps };
                    }
                });
            });
        });
        return newLayout;
    }
});
