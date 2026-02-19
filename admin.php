<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/functions.php';

$currentPageSlug = $_GET['page'] ?? 'home';
$pageInfo = get_page_info($currentPageSlug);

if (!$pageInfo) {
    if ($currentPageSlug === 'home') {
        $pageInfo = ['slug' => 'home', 'title' => 'Home', 'content' => '[]', 'status' => 'published', 'is_home' => 1];
    } else {
        header("Location: admin.php?page=home");
        exit;
    }
}

$blocks = json_decode($pageInfo['content'] ?? '[]', true);
$allPagesFull = get_all_pages_full();
$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 페이지 - 블록-에디터</title>
    <link rel="stylesheet" as="style" crossorigin
        href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #334155;
            overflow-x: hidden;
        }

        .admin-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-bar select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .admin-bar select:hover {
            border-color: #94a3b8;
        }

        .admin-bar button {
            transition: all 0.2s;
            font-weight: 500;
            cursor: pointer;
        }

        .admin-bar button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .btn-secondary {
            background: #cbd5e1;
            color: #334155;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .btn-outline {
            background: white;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 6px;
            color: #475569;
        }

        .admin-wrapper {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .admin-sidebar {
            width: 300px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 20px;
            overflow-y: auto;
            position: sticky;
            top: 60px;
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
        }

        .admin-content {
            flex: 1;
            padding: 40px;
        }

        .block-drawer-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: grab;
            transition: all 0.2s;
        }

        .block-drawer-item:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transform: translateY(-2px);
        }

        .block-drawer-item span {
            font-size: 1.5rem;
            filter: grayscale(0.2);
        }

        .editor-block {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s;
        }

        .editor-block:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .editor-block-header {
            padding: 12px 15px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
        }

        .editor-block-title {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .editor-block-content {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
            margin-bottom: 6px;
            display: block;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            background: white;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            color: #94a3b8;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-published {
            background: #dcfce7;
            color: #166534;
        }

        .status-draft {
            background: #f1f5f9;
            color: #475569;
        }

        .status-trash {
            background: #fee2e2;
            color: #991b1b;
        }

        .home-icon {
            color: #eab308;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    <div class="admin-bar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-weight: bold; font-size: 1.2rem;">블록 에디터</div>

            <select id="pageSelector" onchange="switchPage(this.value)">
                <?php foreach ($allPagesFull as $p): ?>
                    <option value="<?php echo $p['slug']; ?>" <?php echo ($p['slug'] === $currentPageSlug) ? 'selected' : ''; ?>>
                        <?php echo $p['slug']; ?> 
                        <?php echo ($p['is_home'] ? '(홈)' : ''); ?>
                        <?php 
                            if ($p['status'] !== 'published') {
                                $sMap = ['draft' => '초안', 'trash' => '휴지통'];
                                echo '- ' . ($sMap[$p['status']] ?? $p['status']);
                            }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button onclick="openCreateModal()" class="btn-secondary" style="font-size: 0.8rem;">+ 페이지 추가</button>
            <?php if ($currentPageSlug !== 'home'): ?>
                <button onclick="deleteCurrentPage()" class="btn-danger" style="font-size: 0.8rem; padding: 8px 12px; margin-left: 5px;">🗑️ 페이지 삭제</button>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <button onclick="openPageSettingsModal()" class="btn-outline">📄 페이지 설정</button>
            <button onclick="openSiteSettingsModal()" class="btn-outline">⚙️ 사이트 설정</button>
            <a href="index.php?page=<?php echo $currentPageSlug; ?>" target="_blank"
                style="margin: 0 10px; color: #64748b; font-size: 0.9rem; text-decoration: none;">사이트 보기</a>
            <button id="saveBtn" class="btn-primary">저장</button>
            <a href="logout.php"
                style="margin-left: 10px; color: #ef4444; font-size: 0.9rem; font-weight: bold; text-decoration: none;">로그아웃</a>
        </div>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h3
                style="font-size: 1rem; color: #334155; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                사용 가능한 블록</h3>
            <div class="block-drawer">
                <div class="block-drawer-item" draggable="true" data-type="hero">
                    <span style="font-size: 1.5rem;">🖼️</span>
                    <div>
                        <div style="font-weight: 600;">히어로 섹션</div>
                        <div style="font-size: 0.8rem; color: #64748b;">대형 배너 및 타이틀</div>
                    </div>
                </div>
                <div class="block-drawer-item" draggable="true" data-type="text">
                    <span style="font-size: 1.5rem;">📝</span>
                    <div>
                        <div style="font-weight: 600;">텍스트 내용</div>
                        <div style="font-size: 0.8rem; color: #64748b;">일반적인 본문 텍스트</div>
                    </div>
                </div>
                <div class="block-drawer-item" draggable="true" data-type="feature">
                    <span style="font-size: 1.5rem;">✨</span>
                    <div>
                        <div style="font-weight: 600;">주요 기능 (이미지+글)</div>
                        <div style="font-size: 0.8rem; color: #64748b;">좌우 배치형 소개</div>
                    </div>
                </div>
                <div class="block-drawer-item" draggable="true" data-type="cards">
                    <span style="font-size: 1.5rem;">🗂️</span>
                    <div>
                        <div style="font-weight: 600;">카드 그리드</div>
                        <div style="font-size: 0.8rem; color: #64748b;">여러 항목 나열</div>
                    </div>
                </div>
                <div class="block-drawer-item" draggable="true" data-type="cta">
                    <span style="font-size: 1.5rem;">📣</span>
                    <div>
                        <div style="font-weight: 600;">행동 유도 (CTA)</div>
                        <div style="font-size: 0.8rem; color: #64748b;">버튼 강조 섹션</div>
                    </div>
                </div>
            </div>

            <div
                style="margin-top: 40px; background: #eff6ff; padding: 15px; border-radius: 8px; font-size: 0.9rem; color: #1e3a8a;">
                💡 <strong>팁:</strong> 왼쪽의 블록을 오른쪽 영역으로 드래그하여 추가하세요.
            </div>

            <!-- THEME BUTTON AREA -->
            <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <button onclick="openThemeEditor()" class="btn-primary"
                    style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; font-size: 0.95rem; padding: 12px; cursor: pointer;">
                    🎨 테마 설정 (Theme)
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <h2
                style="margin-bottom: 20px; font-size: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                편집 중: <span style="color: #2563eb;"><?php echo htmlspecialchars($currentPageSlug); ?></span>
                <?php if ($pageInfo['status'] !== 'published'): ?>
                    <?php 
                        $statusMap = ['draft' => '초안 (비공개)', 'trash' => '휴지통'];
                        $statusLabel = $statusMap[$pageInfo['status']] ?? $pageInfo['status'];
                    ?>
                    <span class="status-badge status-<?php echo $pageInfo['status']; ?>"><?php echo $statusLabel; ?></span>
                <?php endif; ?>
                <?php if ($pageInfo['is_home']): ?>
                    <span title="This is the Home Page">🏠</span>
                <?php endif; ?>
            </h2>

            <div class="editor-canvas-area">
                <div id="editor-canvas" class="editor-canvas"></div>
                <div id="empty-placeholder"
                    style="text-align: center; padding: 40px; border: 2px dashed #cbd5e1; border-radius: 8px; color: #94a3b8; display: none;">
                    여기에 블록을 드래그하세요
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                새 페이지 만들기
                <span class="modal-close" onclick="closeCreateModal()">×</span>
            </div>
            <div class="form-group">
                <label>페이지 제목</label>
                <input type="text" id="newPageTitle" placeholder="예: 회사 소개">
            </div>
            <div class="form-group">
                <label>페이지 슬러그 (URL)</label>
                <input type="text" id="newPageSlug" placeholder="예: about">
                <small style="color: #64748b;">영문 소문자와 숫자만 사용 가능합니다.</small>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeCreateModal()">취소</button>
                <button class="btn-primary" onclick="createPage()">만들기</button>
            </div>
        </div>
    </div>

    <div id="pageSettingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                페이지 설정 (<?php echo htmlspecialchars($currentPageSlug); ?>)
                <span class="modal-close" onclick="closePageSettingsModal()">×</span>
            </div>
            <div class="form-group">
                <label>SEO: 메타 제목</label>
                <input type="text" id="metaTitle" placeholder="브라우저 탭에 표시될 제목"
                    value="<?php echo htmlspecialchars($pageInfo['meta_title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>SEO: 메타 설명</label>
                <textarea id="metaDescription"
                    rows="3"><?php echo htmlspecialchars($pageInfo['meta_description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>페이지 상태</label>
                <select id="pageStatus">
                    <option value="published" <?php echo ($pageInfo['status'] === 'published') ? 'selected' : ''; ?>>공개 (Published)</option>
                    <option value="draft" <?php echo ($pageInfo['status'] === 'draft') ? 'selected' : ''; ?>>초안 (Draft)</option>
                    <option value="trash" <?php echo ($pageInfo['status'] === 'trash') ? 'selected' : ''; ?>>휴지통 (Trash)</option>
                </select>
            </div>
            <div class="modal-actions" style="justify-content: space-between;">
                <div>
                    <?php if (!$pageInfo['is_home']): ?>
                        <button class="btn-secondary" onclick="setAsHome()">🏠 홈으로 지정</button>
                    <?php else: ?>
                        <span style="color: #eab308; font-weight: bold;">✅ 현재 홈 페이지입니다</span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($currentPageSlug !== 'home'): ?>
                        <button class="btn-danger" onclick="deleteCurrentPage()">삭제 (휴지통)</button>
                    <?php endif; ?>
                    <button class="btn-primary" onclick="savePageSettings()">저장</button>
                </div>
            </div>
        </div>
    </div>

    <div id="siteSettingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                사이트 설정
                <span class="modal-close" onclick="closeSiteSettingsModal()">×</span>
            </div>
            <div class="form-group">
                <label>사이트 제목</label>
                <input type="text" id="siteTitle"
                    value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Modern Tech & Minimal Life'); ?>">
            </div>
            <div class="form-group">
                <label>푸터 문구</label>
                <input type="text" id="footerText"
                    value="<?php echo htmlspecialchars($settings['footer_text'] ?? '© 2026 Block Web.'); ?>">
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeSiteSettingsModal()">취소</button>
                <button class="btn-primary" onclick="saveSiteSettings()">설정 저장</button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal (For block deletion) -->
    <div id="confirmModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <div class="modal-header">
                확인
                <span class="modal-close" onclick="closeConfirmModal()">×</span>
            </div>
            <div id="confirmMessage" style="margin-bottom: 20px; font-size: 1rem; color: #334155;">
                정말 삭제하시겠습니까?
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeConfirmModal()">아니오</button>
                <button id="confirmYesBtn" class="btn-danger">예, 삭제합니다</button>
            </div>
        </div>
    </div>

    <script>
        const initialBlocks = <?php echo json_encode($blocks); ?>;
        const currentPage = "<?php echo $currentPageSlug; ?>";
        const allPages = <?php echo json_encode(array_column($allPagesFull, 'slug')); ?>; 
    </script>
    <script src="assets/js/editor.js"></script>
    <script>
        function openPageSettingsModal() { document.getElementById('pageSettingsModal').style.display = 'flex'; }
        function closePageSettingsModal() { document.getElementById('pageSettingsModal').style.display = 'none'; }

        function savePageSettings() {
            const metaTitle = document.getElementById('metaTitle').value;
            const metaDescription = document.getElementById('metaDescription').value;
            const status = document.getElementById('pageStatus').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_page',
                    page: currentPage,
                    content: blocks,
                    meta_title: metaTitle,
                    meta_description: metaDescription,
                    status: status
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('페이지 설정이 저장되었습니다.');
                        location.reload();
                    } else {
                        alert('실패: ' + data.message);
                    }
                });
        }

        function setAsHome() {
            if (!confirm('이 페이지를 홈 페이지로 설정하시겠습니까?')) return;
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set_home', slug: currentPage })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { alert('홈 페이지로 설정되었습니다.'); location.reload(); }
                    else { alert('실패: ' + data.message); }
                });
        }

        function openSiteSettingsModal() { document.getElementById('siteSettingsModal').style.display = 'flex'; }
        function closeSiteSettingsModal() { document.getElementById('siteSettingsModal').style.display = 'none'; }

        function saveSiteSettings() {
            const siteTitle = document.getElementById('siteTitle').value;
            const footerText = document.getElementById('footerText').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_settings',
                    settings: { site_title: siteTitle, footer_text: footerText }
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { alert('사이트 설정이 저장되었습니다.'); location.reload(); }
                    else { alert('실패: ' + data.message); }
                });
        }

        // Custom Confirm handling
        let confirmCallback = null;
        window.openConfirmModal = function (msg, callback) {
            document.getElementById('confirmMessage').innerText = msg;
            confirmCallback = callback;
            document.getElementById('confirmModal').style.display = 'flex';
        }
        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            confirmCallback = null;
        }
        document.getElementById('confirmYesBtn').addEventListener('click', function () {
            if (confirmCallback) confirmCallback();
            closeConfirmModal();
        });

        // Save Button (Top Bar) Logic
        document.getElementById('saveBtn').replaceWith(document.getElementById('saveBtn').cloneNode(true));
        document.getElementById('saveBtn').addEventListener('click', () => {
            const btn = document.getElementById('saveBtn');
            const originalText = btn.innerText;
            btn.innerText = '저장 중...';
            btn.disabled = true;

            const metaTitle = document.getElementById('metaTitle').value;
            const metaDescription = document.getElementById('metaDescription').value;
            const status = document.getElementById('pageStatus').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', },
                body: JSON.stringify({
                    action: 'save_page',
                    page: currentPage,
                    content: blocks,
                    meta_title: metaTitle,
                    meta_description: metaDescription,
                    status: status
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) { alert('성공적으로 저장되었습니다!'); }
                    else { alert('저장 실패: ' + (data.message || '알 수 없는 오류')); }
                })
                .catch(error => { console.error('Error:', error); alert('저장 중 오류가 발생했습니다.'); })
                .finally(() => { btn.innerText = originalText; btn.disabled = false; });
        });
    </script>
</body>

</html>