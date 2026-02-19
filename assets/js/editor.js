let blocks = initialBlocks || [];
let draggedItem = null;
let draggedSource = null; // 'drawer' or 'canvas'
let currentView = 'blocks'; // 'blocks' or 'theme'

const canvas = document.getElementById('editor-canvas');
const placeholder = document.getElementById('empty-placeholder');

// Initialize
function init() {
    renderEditor(); // Handles checking currentView
    setupDrawerListeners();
    setupCanvasListeners();
}

// --- View Switching ---

window.openThemeEditor = function () {
    currentView = 'theme';
    // Hide placeholder explicitly when in theme mode
    if (placeholder) placeholder.style.display = 'none';
    renderEditor();
}

window.closeThemeEditor = function () {
    currentView = 'blocks';
    renderEditor();
}

// --- Theme Logic ---

function renderThemeEditor() {
    // Current Theme Values (Read from computed style or inputs if we had them, 
    // but better to rely on what php injected or defaults)
    // We will assume we can get them from CSS variables for initial state
    const rootStyle = getComputedStyle(document.documentElement);
    const primary = rootStyle.getPropertyValue('--color-primary').trim() || '#0F172A';
    const secondary = rootStyle.getPropertyValue('--color-secondary').trim() || '#2563EB';
    const bg = rootStyle.getPropertyValue('--color-bg').trim() || '#F8FAFC';
    const text = rootStyle.getPropertyValue('--color-text').trim() || '#334155';

    let html = `
        <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">🎨 테마 설정 (Theme Settings)</h2>
            
            <div style="margin-bottom: 30px;">
                <h4 style="margin-bottom: 10px; color: #64748b;">추천 프리셋 (Presets)</h4>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button onclick="applyPreset('default')" class="btn" style="background:#fff; color:#333; border:1px solid #ddd;">기본(Default)</button>
                    <button onclick="applyPreset('ocean')" class="btn" style="background:#0ea5e9; color:white;">Ocean</button>
                    <button onclick="applyPreset('forest')" class="btn" style="background:#10b981; color:white;">Forest</button>
                    <button onclick="applyPreset('sunset')" class="btn" style="background:#f97316; color:white;">Sunset</button>
                    <button onclick="applyPreset('dark')" class="btn" style="background:#0f172a; color:white;">Dark</button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label>메인 컬러 (Primary)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="color" id="themePrimary" value="${primary}" oninput="updateThemePreview('primary', this.value)" style="height:40px; width:60px;">
                        <input type="text" id="themePrimaryText" value="${primary}" oninput="updateThemePreview('primary', this.value); document.getElementById('themePrimary').value=this.value;" style="flex:1; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>포인트 컬러 (Accent/Secondary)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="color" id="themeSecondary" value="${secondary}" oninput="updateThemePreview('secondary', this.value)" style="height:40px; width:60px;">
                        <input type="text" id="themeSecondaryText" value="${secondary}" oninput="updateThemePreview('secondary', this.value); document.getElementById('themeSecondary').value=this.value;" style="flex:1; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>배경색 (Background)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="color" id="themeBg" value="${bg}" oninput="updateThemePreview('bg', this.value)" style="height:40px; width:60px;">
                        <input type="text" id="themeBgText" value="${bg}" oninput="updateThemePreview('bg', this.value); document.getElementById('themeBg').value=this.value;" style="flex:1; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>글자색 (Text)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="color" id="themeText" value="${text}" oninput="updateThemePreview('text', this.value)" style="height:40px; width:60px;">
                        <input type="text" id="themeTextText" value="${text}" oninput="updateThemePreview('text', this.value); document.getElementById('themeText').value=this.value;" style="flex:1; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <button onclick="closeThemeEditor()" class="btn" style="background:#e2e8f0; color:#334155;">취소 (돌아가기)</button>
                <button onclick="saveThemeSettings()" class="btn" style="background:#2563eb; color:white;">💾 저장 및 적용</button>
            </div>
        </div>
    `;
    return html;
}

window.updateThemePreview = function (key, value) {
    const cssVarMap = {
        'primary': '--color-primary',
        'secondary': '--color-secondary',
        'bg': '--color-bg',
        'text': '--color-text'
    };
    if (cssVarMap[key]) {
        document.documentElement.style.setProperty(cssVarMap[key], value);
    }
}

window.applyPreset = function (presetName) {
    const presets = {
        'default': { primary: '#0F172A', secondary: '#2563EB', bg: '#F8FAFC', text: '#334155' },
        'ocean': { primary: '#0c4a6e', secondary: '#0ea5e9', bg: '#f0f9ff', text: '#0c4a6e' },
        'forest': { primary: '#064e3b', secondary: '#10b981', bg: '#ecfdf5', text: '#064e3b' },
        'sunset': { primary: '#7c2d12', secondary: '#f97316', bg: '#fff7ed', text: '#7c2d12' },
        'dark': { primary: '#f8fafc', secondary: '#6366f1', bg: '#0f172a', text: '#f8fafc' } // Inverted for dark mode simulation
    };

    const p = presets[presetName];
    if (!p) return;

    // Apply to DOM
    updateThemePreview('primary', p.primary);
    updateThemePreview('secondary', p.secondary);
    updateThemePreview('bg', p.bg);
    updateThemePreview('text', p.text);

    // Update Inputs
    document.getElementById('themePrimary').value = p.primary;
    document.getElementById('themePrimaryText').value = p.primary;

    document.getElementById('themeSecondary').value = p.secondary;
    document.getElementById('themeSecondaryText').value = p.secondary;

    document.getElementById('themeBg').value = p.bg;
    document.getElementById('themeBgText').value = p.bg;

    document.getElementById('themeText').value = p.text;
    document.getElementById('themeTextText').value = p.text;
}

window.saveThemeSettings = function () {
    const theme = {
        theme_primary: document.getElementById('themePrimary').value,
        theme_secondary: document.getElementById('themeSecondary').value,
        theme_bg: document.getElementById('themeBg').value,
        theme_text: document.getElementById('themeText').value
    };

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_settings',
            settings: theme
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('테마가 저장되었습니다!');
                closeThemeEditor();
            } else {
                alert('저장 실패: ' + data.message);
            }
        });
}

// --- Editor Rendering ---

function renderEditor() {
    canvas.innerHTML = '';

    // If Theme View
    if (currentView === 'theme') {
        canvas.innerHTML = renderThemeEditor();
        return;
    }

    // Blocks View
    if (blocks.length === 0) {
        if (placeholder) placeholder.style.display = 'block';
    } else {
        if (placeholder) placeholder.style.display = 'none';

        blocks.forEach((block, index) => {
            const el = document.createElement('div');
            el.className = 'editor-block';
            el.draggable = true;
            el.dataset.index = index;

            // Apply block styles (Custom Overrides)
            if (block.content.blockBgColor) el.style.backgroundColor = block.content.blockBgColor;
            if (block.content.blockTextColor) el.style.color = block.content.blockTextColor;

            let blockName = '';
            switch (block.type) {
                case 'hero': blockName = '히어로 섹션'; break;
                case 'text': blockName = '텍스트 내용'; break;
                case 'feature': blockName = '주요 기능'; break;
                case 'cards': blockName = '카드 그리드'; break;
                case 'cta': blockName = '행동 유도 (CTA)'; break;
                default: blockName = block.type;
            }

            el.innerHTML = `
                <div class="editor-block-header">
                    <span class="editor-block-title">≡ ${blockName}</span>
                    <div class="editor-controls">
                        <button class="btn-icon btn-delete" onclick="deleteBlock(${index})" title="삭제" style="background: none; border: none; cursor: pointer; font-size: 1.5rem; line-height:1; color: #ef4444; transition: opacity 0.2s;">
                            🗑️
                        </button>
                    </div>
                </div>
                <div class="editor-block-content">
                    ${getFieldsForType(block.type, block.content, index)}
                </div>
            `;

            // Drag Events
            el.addEventListener('dragstart', handleBlockDragStart);
            el.addEventListener('dragend', handleBlockDragEnd);
            el.addEventListener('dragover', handleBlockDragOver);
            el.addEventListener('drop', handleBlockDrop);

            canvas.appendChild(el);
        });

        setupImageDropListeners();
    }
}

function getFieldsForType(type, content, index) {
    let html = '';

    const createInput = (key, label, inputType = 'text', options = []) => {
        const value = content[key] || '';

        if (inputType === 'textarea') {
            return `
                <div class="form-group">
                    <label>${label}</label>
                    <textarea rows="4" oninput="updateContent(${index}, '${key}', this.value)">${value}</textarea>
                </div>
            `;
        } else if (inputType === 'select') {
            return `
                <div class="form-group">
                    <label>${label}</label>
                    <select onchange="updateContent(${index}, '${key}', this.value)">
                        ${options.map(opt => `<option value="${opt.value}" ${value === opt.value ? 'selected' : ''}>${opt.label}</option>`).join('')}
                    </select>
                </div>
            `;
        }

        if (inputType === 'image') {
            return `
                <div class="form-group">
                    <label>${label}</label>
                    <div class="image-upload-container" style="display: flex; gap: 8px; align-items: center;">
                        <input type="text" value="${value}" 
                            oninput="updateContent(${index}, '${key}', this.value)"
                            placeholder="이미지 URL 또는 파일 업로드"
                            style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <input type="file" id="file-${index}-${key}" style="display: none;" 
                            accept="image/*" onchange="handleFileUpload(this, ${index}, '${key}')">
                        <button onclick="document.getElementById('file-${index}-${key}').click()" 
                            style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; cursor: pointer;">
                            📁 업로드
                        </button>
                    </div>
                    <div class="image-drop-zone" data-index="${index}" data-key="${key}"
                        style="margin-top: 5px; border: 2px dashed #cbd5e1; border-radius: 6px; padding: 10px; text-align: center; color: #94a3b8; font-size: 0.8rem; cursor: pointer;"
                        onclick="document.getElementById('file-${index}-${key}').click()">
                        ${value ? `<img src="${value}" style="max-height: 100px; display: block; margin: 0 auto 5px;">` : ''}
                        이미지를 이곳에 드래그하거나 클릭하여 업로드하세요
                    </div>
                </div>
            `;
        }

        if (inputType === 'link') {
            // ... (Same Link Logic) ...
            let initialMode = 'custom';
            const pages = typeof allPages !== 'undefined' ? allPages : [];
            if (pages.includes(value) || (value && value.startsWith('?page=') && pages.includes(value.replace('?page=', '')))) {
                initialMode = 'page';
            }
            const pageOptionsHtml = pages.map(p => {
                const isSelected = (value === p || value === `?page=${p}`);
                return `<option value="${p}" ${isSelected ? 'selected' : ''}>📄 ${p}</option>`;
            }).join('');

            return `
                <div class="form-group link-control">
                    <label>${label}</label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <select onchange="handleLinkTypeChange(this, ${index}, '${key}')" 
                            style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc;">
                            <option value="__custom__" ${initialMode === 'custom' ? 'selected' : ''}>🔗 직접 입력 (Direct URL)</option>
                            <optgroup label="내 페이지">
                                ${pageOptionsHtml}
                            </optgroup>
                        </select>
                        <input type="text" id="input-${index}-${key}" value="${value}" 
                            placeholder="https://... 또는 페이지 주소"
                            style="display: ${initialMode === 'custom' ? 'block' : 'none'}; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;"
                            oninput="updateContent(${index}, '${key}', this.value)">
                        <div class="selected-page-preview" 
                             style="display: ${initialMode === 'page' ? 'block' : 'none'}; font-size: 0.9rem; color: #2563eb; padding: 8px; background: #eff6ff; border-radius: 4px;">
                            ✅ 선택된 페이지로 연결됩니다: <b>${value}</b>
                        </div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="form-group">
                <label>${label}</label>
                <input type="${inputType}" value="${value}" oninput="updateContent(${index}, '${key}', this.value)">
            </div>
        `;
    };

    // Block Inputs
    if (type === 'hero') {
        html += createInput('title', '메인 타이틀');
        html += createInput('subtitle', '서브 타이틀', 'textarea');
        html += createInput('buttonText', '버튼 텍스트');
        html += createInput('buttonLink', '버튼 링크', 'link');
    } else if (type === 'text') {
        html += createInput('heading', '제목');
        html += createInput('text', '본문 내용', 'textarea');
    } else if (type === 'feature') {
        html += createInput('heading', '제목');
        html += createInput('text', '설명', 'textarea');
        html += createInput('imageUrl', '이미지 URL', 'image');
        html += createInput('buttonText', '버튼 텍스트');
        html += createInput('buttonLink', '버튼 링크', 'link');
        html += createInput('imagePosition', '이미지 위치', 'select', [
            { value: 'left', label: '왼쪽' },
            { value: 'right', label: '오른쪽' }
        ]);
    } else if (type === 'cta') {
        html += createInput('heading', '강조 문구');
        html += createInput('text', '부가 설명', 'textarea');
        html += createInput('buttonText', '버튼 텍스트');
        html += createInput('buttonLink', '버튼 링크', 'link');
    } else if (type === 'cards') {
        html += createInput('columns', '카드 컬럼 수', 'select', [
            { value: 'auto', label: '자동 (반응형)' },
            { value: '2', label: '2개씩 보기' },
            { value: '3', label: '3개씩 보기' },
            { value: '4', label: '4개씩 보기' }
        ]);
        const cards = content.cards || [];
        html += `<div style="margin-bottom:10px; font-weight:bold; color:#334155;">카드 목록</div>`;
        cards.forEach((card, cIndex) => {
            html += `
                <div style="background: #f8fafc; padding: 15px; margin-bottom: 10px; border-radius: 6px; border:1px solid #e2e8f0;">
                    <div style="margin-bottom: 10px; font-size: 0.85rem; font-weight:bold; display:flex; justify-content:space-between;">
                        <span>카드 #${cIndex + 1}</span>
                        <button onclick="removeCard(${index}, ${cIndex})" style="color: #ef4444; cursor:pointer; background:none; border:none;">삭제</button>
                    </div>
                    <input type="text" placeholder="카드 제목" value="${card.title || ''}" 
                        style="width: 100%; margin-bottom: 8px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;"
                        oninput="updateCardContent(${index}, ${cIndex}, 'title', this.value)">
                    <textarea placeholder="카드 내용" 
                        style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;"
                        oninput="updateCardContent(${index}, ${cIndex}, 'text', this.value)">${card.text || ''}</textarea>
                </div>
            `;
        });
        html += `<button onclick="addCard(${index})" class="btn" style="font-size: 0.9rem; padding: 8px 16px; background-color:#475569;">+ 카드 추가</button>`;
    }

    // Styles & Layout Settings
    const bgColor = content.blockBgColor || '#ffffff';
    const textColor = content.blockTextColor || '#334155';

    html += `
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #e2e8f0;">
            <h4 style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; font-weight: bold;">🎨 스타일 (Styles)</h4>
            
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">배경색 (Background)</label>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <input type="color" value="${bgColor}" oninput="updateContent(${index}, 'blockBgColor', this.value); renderEditor();" style="height:35px; width:40px; padding:2px;">
                        <button onclick="updateContent(${index}, 'blockBgColor', ''); renderEditor();" style="font-size:0.7rem; padding: 4px 8px; background:white; border:1px solid #cbd5e1; cursor:pointer; border-radius:4px;">초기화</button>
                    </div>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">글자색 (Text)</label>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <input type="color" value="${textColor}" oninput="updateContent(${index}, 'blockTextColor', this.value); renderEditor();" style="height:35px; width:40px; padding:2px;">
                        <button onclick="updateContent(${index}, 'blockTextColor', ''); renderEditor();" style="font-size:0.7rem; padding: 4px 8px; background:white; border:1px solid #cbd5e1; cursor:pointer; border-radius:4px;">초기화</button>
                    </div>
                </div>
            </div>
            <p style="color:#94a3b8; font-size:0.8rem;">색상을 변경하면 실시간으로 미리볼 수 있습니다.</p>
        </div>
    `;

    return html;
}

// ... (Rest of logic: Image Drop, API calls, Drag Drop) ...

function setupImageDropListeners() {
    const zones = document.querySelectorAll('.image-drop-zone');
    zones.forEach(zone => {
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.style.borderColor = '#2563eb'; zone.style.backgroundColor = '#eff6ff'; });
        zone.addEventListener('dragleave', (e) => { zone.style.borderColor = '#cbd5e1'; zone.style.backgroundColor = 'transparent'; });
        zone.addEventListener('drop', (e) => { e.preventDefault(); zone.style.borderColor = '#cbd5e1'; zone.style.backgroundColor = 'transparent'; const files = e.dataTransfer.files; if (files.length > 0) { const index = zone.dataset.index; const key = zone.dataset.key; uploadImage(files[0], index, key); } });
    });
}
function handleFileUpload(input, index, key) { if (input.files.length > 0) { uploadImage(input.files[0], index, key); } }
function uploadImage(file, index, key) { const formData = new FormData(); formData.append('image', file); const zone = document.querySelector(`.image-drop-zone[data-index="${index}"][data-key="${key}"]`); const originalText = zone ? zone.innerHTML : ''; if (zone) zone.innerText = '업로드 중...'; fetch('api/upload_image.php', { method: 'POST', body: formData }).then(response => response.json()).then(data => { if (data.url) { updateContent(index, key, data.url); renderEditor(); } else { alert('업로드 실패: ' + (data.error || '알 수 없는 오류')); if (zone) zone.innerHTML = originalText; } }).catch(error => { console.error('Error:', error); alert('업로드 중 오류가 발생했습니다.'); if (zone) zone.innerHTML = originalText; }); }
function updateContent(index, key, value) { blocks[index].content[key] = value; }
function updateCardContent(blockIndex, cardIndex, key, value) { if (!blocks[blockIndex].content.cards[cardIndex]) return; blocks[blockIndex].content.cards[cardIndex][key] = value; }
function addCard(blockIndex) { if (!blocks[blockIndex].content.cards) blocks[blockIndex].content.cards = []; blocks[blockIndex].content.cards.push({ title: '새로운 카드', text: '카드 내용을 입력하세요.' }); renderEditor(); }

// --- CUSTOM CONFIRM LOGIC ---
function removeCard(blockIndex, cardIndex) {
    if (window.openConfirmModal) {
        window.openConfirmModal('정말 이 카드를 삭제하시겠습니까?', function () {
            blocks[blockIndex].content.cards.splice(cardIndex, 1);
            renderEditor();
        });
    } else {
        if (confirm('정말 이 카드를 삭제하시겠습니까?')) { blocks[blockIndex].content.cards.splice(cardIndex, 1); renderEditor(); }
    }
}
function deleteBlock(index) {
    if (window.openConfirmModal) {
        window.openConfirmModal('정말 이 블록을 삭제하시겠습니까?\n삭제된 블록은 복구할 수 없습니다.', function () {
            blocks.splice(index, 1);
            renderEditor();
        });
    } else {
        if (confirm('정말 이 블록을 삭제하시겠습니까?')) { blocks.splice(index, 1); renderEditor(); }
    }
}
// ----------------------------

function setupDrawerListeners() { const items = document.querySelectorAll('.block-drawer-item'); items.forEach(item => { item.addEventListener('dragstart', (e) => { draggedItem = item; draggedSource = 'drawer'; e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('type', item.dataset.type); item.style.opacity = '0.5'; }); item.addEventListener('dragend', (e) => { item.style.opacity = '1'; draggedItem = null; draggedSource = null; clearDropZones(); }); }); }
function setupCanvasListeners() { placeholder.addEventListener('dragover', (e) => { e.preventDefault(); placeholder.style.backgroundColor = '#f0f9ff'; placeholder.style.borderColor = '#2563eb'; }); placeholder.addEventListener('dragleave', (e) => { placeholder.style.backgroundColor = 'transparent'; placeholder.style.borderColor = '#cbd5e1'; }); placeholder.addEventListener('drop', (e) => { e.preventDefault(); placeholder.style.backgroundColor = 'transparent'; placeholder.style.borderColor = '#cbd5e1'; if (draggedSource === 'drawer') { const type = e.dataTransfer.getData('type'); addNewBlock(type); } }); canvas.addEventListener('dragover', (e) => { e.preventDefault(); }); }
function handleBlockDragStart(e) { draggedItem = this; draggedSource = 'canvas'; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('index', this.dataset.index); this.classList.add('dragging'); }
function handleBlockDragEnd(e) { this.classList.remove('dragging'); draggedItem = null; draggedSource = null; document.querySelectorAll('.editor-block').forEach(el => { el.style.borderTop = ''; el.style.borderBottom = ''; }); }
function handleBlockDragOver(e) { if (e.preventDefault) { e.preventDefault(); } if (e.target.closest('.image-drop-zone')) { return true; } const rect = this.getBoundingClientRect(); const relY = e.clientY - rect.top; const height = rect.height; document.querySelectorAll('.editor-block').forEach(el => { el.style.borderTop = ''; el.style.borderBottom = ''; }); if (relY > height / 2) { this.style.borderBottom = '2px solid #2563eb'; this.dataset.dropPos = 'after'; } else { this.style.borderTop = '2px solid #2563eb'; this.dataset.dropPos = 'before'; } return false; }
function handleBlockDrop(e) { e.stopPropagation(); if (e.target.closest('.image-drop-zone')) { return; } this.style.borderTop = ''; this.style.borderBottom = ''; const targetIndex = parseInt(this.dataset.index); const dropPos = this.dataset.dropPos; if (draggedSource === 'drawer') { const type = e.dataTransfer.getData('type'); let newIndex = targetIndex; if (dropPos === 'after') newIndex++; addNewBlock(type, newIndex); } else if (draggedSource === 'canvas') { const sourceIndex = parseInt(e.dataTransfer.getData('index')); if (sourceIndex === targetIndex) return; const itemToMove = blocks[sourceIndex]; blocks.splice(sourceIndex, 1); let finalIndex = targetIndex; if (sourceIndex < targetIndex) finalIndex--; if (dropPos === 'after') finalIndex++; blocks.splice(finalIndex, 0, itemToMove); renderEditor(); } return false; }
function clearDropZones() { document.querySelectorAll('.editor-block').forEach(el => { el.style.borderTop = ''; el.style.borderBottom = ''; }); }
function addNewBlock(type, index = null) { const newBlock = { id: 'block_' + Date.now(), type: type, content: {} }; if (type === 'hero') { newBlock.content = { title: '새로운 히어로 타이틀', subtitle: '여기에 서브타이틀을 입력하세요', buttonText: '자세히 보기', buttonLink: '#' }; } else if (type === 'text') { newBlock.content = { heading: '새로운 섹션', text: '내용을 입력하세요.' }; } else if (type === 'cards') { newBlock.content = { cards: [{ title: '카드 1', text: '설명 1' }, { title: '카드 2', text: '설명 2' }] }; } else if (type === 'feature') { newBlock.content = { heading: '주요 기능 제목', text: '기능에 대한 상세 설명을 여기에 적으세요.', imageUrl: 'https://placehold.co/600x400', buttonText: '더 알아보기', buttonLink: '#' }; } else if (type === 'cta') { newBlock.content = { heading: '지금 시작하세요', text: '여러분의 비즈니스를 한 단계 업그레이드하세요.', buttonText: '가입하기', buttonLink: '#' }; } if (index === null) { blocks.push(newBlock); } else { blocks.splice(index, 0, newBlock); } renderEditor(); }
document.getElementById('saveBtn').addEventListener('click', () => { const btn = document.getElementById('saveBtn'); const originalText = btn.innerText; btn.innerText = '저장 중...'; btn.disabled = true; fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', }, body: JSON.stringify({ action: 'save_page', page: currentPage, content: blocks }), }).then(response => response.json()).then(data => { if (data.success) { alert('성공적으로 저장되었습니다!'); } else { alert('저장 실패: ' + (data.message || '알 수 없는 오류')); } }).catch(error => { console.error('Error:', error); alert('저장 중 오류가 발생했습니다.'); }).finally(() => { btn.innerText = originalText; btn.disabled = false; }); });
function switchPage(page) { window.location.href = `admin.php?page=${page}`; }
function openCreateModal() { document.getElementById('createModal').style.display = 'flex'; }
function closeCreateModal() { document.getElementById('createModal').style.display = 'none'; }
function createPage() { const title = document.getElementById('newPageTitle').value; const slug = document.getElementById('newPageSlug').value; if (!title || !slug) { alert('페이지 제목과 슬러그를 모두 입력해주세요.'); return; } fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', }, body: JSON.stringify({ action: 'create_page', title: title, slug: slug }), }).then(response => response.json()).then(data => { if (data.success) { alert('페이지가 생성되었습니다!'); window.location.href = `admin.php?page=${slug}`; } else { alert('실패: ' + (data.message || '페이지 생성 중 오류가 발생했습니다.')); } }).catch(error => { console.error('Error:', error); alert('오류가 발생했습니다.'); }); }
function deleteCurrentPage() { if (currentPage === 'home') { alert('홈 페이지는 삭제할 수 없습니다.'); return; } if (!confirm(`정말 '${currentPage}' 페이지를 삭제하시겠습니까?\n삭제 후에는 복구할 수 없습니다.`)) { return; } fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', }, body: JSON.stringify({ action: 'delete_page', slug: currentPage }), }).then(response => response.json()).then(data => { if (data.success) { alert('페이지가 삭제되었습니다.'); window.location.href = `admin.php?page=home`; } else { alert('삭제 실패: ' + (data.message || '오류가 발생했습니다.')); } }).catch(error => { console.error('Error:', error); alert('오류가 발생했습니다.'); }); }
function handleLinkTypeChange(select, index, key) { const val = select.value; const container = select.parentElement; const input = container.querySelector('input'); const preview = container.querySelector('.selected-page-preview'); if (val === '__custom__') { input.style.display = 'block'; preview.style.display = 'none'; input.focus(); } else { input.style.display = 'none'; preview.style.display = 'block'; preview.innerHTML = `✅ 선택된 페이지로 연결됩니다: <b>${val}</b>`; updateContent(index, key, val); input.value = val; } }
window.addEventListener('DOMContentLoaded', init);
