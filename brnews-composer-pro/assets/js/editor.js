document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const tabs = document.querySelectorAll('.panel-tabs .tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const targetId = tab.dataset.target;
            document.querySelectorAll('.panel-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(targetId).style.display = 'block';
        });
    });

    // Inspector toggle
    const inspectorToggle = document.getElementById('brcp-toggle-right');
    const rightPanel = document.getElementById('brcp-right');
    const shell = document.getElementById('brcp-shell');

    if (inspectorToggle && rightPanel && shell) {
        inspectorToggle.addEventListener('click', () => {
            const isHidden = rightPanel.style.display === 'none';
            rightPanel.style.display = isHidden ? 'flex' : 'none';
            inspectorToggle.classList.toggle('active', isHidden);

            if (!isHidden) {
                shell.style.gridTemplateColumns = '280px 1fr';
            } else {
                shell.style.gridTemplateColumns = '280px 1fr 320px';
            }
        });
    }

    const blockLibraryContainer = document.getElementById('brcp-block-library');
    const searchInput = document.querySelector('.panel-search input');
    let allGroups = [];

    function renderBlockLibrary(groups) {
        blockLibraryContainer.innerHTML = '';
        groups.forEach(group => {
            const groupEl = document.createElement('div');
            groupEl.classList.add('brcp-pal-group');

            const groupHeader = document.createElement('div');
            groupHeader.classList.add('brcp-pal-header');
            groupHeader.innerHTML = `<span class="brcp-pal-title">${group.title}</span>`;
            groupEl.appendChild(groupHeader);

            const groupItems = document.createElement('div');
            groupItems.classList.add('brcp-pal-list');

            group.items.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.classList.add('brcp-pal-item');
                itemEl.dataset.type = item.type;
                itemEl.dataset.preset = JSON.stringify(item.preset);
                itemEl.draggable = true;

                itemEl.innerHTML = `
                    <div class="brcp-pal-item-icon"><span class="dashicons ${item.icon || 'dashicons-block-default'}"></span></div>
                    <div class="brcp-pal-item-content">
                        <div class="brcp-pal-item-label">${item.label}</div>
                        <p class="brcp-pal-item-desc">${item.description}</p>
                    </div>
                `;

                itemEl.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        type: item.type,
                        preset: item.preset
                    }));
                    e.target.classList.add('dragging');
                });
                itemEl.addEventListener('dragend', (e) => {
                    e.target.classList.remove('dragging');
                });

                groupItems.appendChild(itemEl);
            });

            groupEl.appendChild(groupItems);
            blockLibraryContainer.appendChild(groupEl);
        });
    }

    // Fetch and render block library
    if (blockLibraryContainer) {
        wp.apiFetch({ path: '/brcp/v1/library' }).then(groups => {
            allGroups = groups;
            renderBlockLibrary(allGroups);
        });
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredGroups = allGroups.map(group => {
                const filteredItems = group.items.filter(item =>
                    item.label.toLowerCase().includes(searchTerm) ||
                    item.description.toLowerCase().includes(searchTerm)
                );
                return { ...group, items: filteredItems };
            }).filter(group => group.items.length > 0);

            renderBlockLibrary(filteredGroups);
        });
    }

    const iframe = document.getElementById('brcp-iframe');
    const dropZone = document.createElement('div');
    dropZone.id = 'brcp-dropzone';
    iframe.parentNode.insertBefore(dropZone, iframe.nextSibling);

    let currentLayout = { rows: [] };
    let selectedModuleId = null;
    const postId = new URLSearchParams(window.location.search).get('post_id');

    // Fetch initial layout
    if (postId) {
        wp.apiFetch({ path: `/wp/v2/posts/${postId}?context=edit` }).then(post => {
            if (post.meta._brcp_layout && post.meta._brcp_layout[0]) {
                try {
                    currentLayout = JSON.parse(post.meta._brcp_layout[0]);
                } catch (e) {
                    console.error('Error parsing layout JSON:', e);
                }
            }
        });
    }

    // Drag and drop
    let draggedItem = null;

    document.addEventListener('dragstart', (e) => {
        if (e.target.closest('.brcp-pal-item')) {
            draggedItem = e.target.closest('.brcp-pal-item');
            dropZone.classList.add('show');
        }
    });

    document.addEventListener('dragend', () => {
        draggedItem = null;
        dropZone.classList.remove('show');
    });

    iframe.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    iframe.contentWindow.addEventListener('dragover', (e) => {
        e.preventDefault();
        // This is tricky, we need to show the drop indicator in the iframe
    });

    iframe.contentWindow.addEventListener('drop', (e) => {
        e.preventDefault();
        if (draggedItem) {
            const blockPreset = JSON.parse(draggedItem.dataset.preset);
            const blockType = draggedItem.dataset.type;

            const newModule = {
                id: `brcp-module-${Date.now()}`,
                type: blockType,
                props: blockPreset
            };

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
                inspector.innerHTML = `<div class="insp-group"><div class="insp-title">${block.label}</div><div class="insp-body"></div></div>`;
                const inspectorBody = inspector.querySelector('.insp-body');

                if (block.fields) {
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
                        inspectorBody.appendChild(fieldWrapper);
                    });
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
        const newLayout = JSON.parse(JSON.stringify(layout));
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
