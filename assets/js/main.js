// Main JavaScript file for Roblox Group Management System

// Global variables
let currentGroupId = null;
let currentMembers = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadSavedData();
});

// Initialize event listeners
function initializeEventListeners() {
    // Group lookup form
    const groupLookupForm = document.getElementById('groupLookupForm');
    if (groupLookupForm) {
        groupLookupForm.addEventListener('submit', handleGroupLookup);
    }

    // Player search form
    const playerSearchForm = document.getElementById('playerSearchForm');
    if (playerSearchForm) {
        playerSearchForm.addEventListener('submit', handlePlayerSearch);
    }

    // Auto-refresh functionality
    setInterval(checkForUpdates, 60000); // Check every minute
}

// Handle group lookup
async function handleGroupLookup(event) {
    event.preventDefault();
    
    const groupId = document.getElementById('groupId').value;
    const resultsDiv = document.getElementById('groupResults');
    
    if (!groupId) {
        showError(resultsDiv, 'Lütfen bir grup ID girin');
        return;
    }

    showLoading(resultsDiv);

    try {
        const response = await fetch(`api/group_lookup.php?groupId=${groupId}`);
        const text = await response.text();
        
        // Clean the response from PHP warnings
        const jsonStart = text.indexOf('{');
        const cleanText = jsonStart !== -1 ? text.substring(jsonStart) : text;
        
        const data = JSON.parse(cleanText);

        if (data.success) {
            displayGroupResults(data, resultsDiv);
            currentGroupId = groupId;
            saveToLocalStorage('lastGroupId', groupId);
        } else {
            showError(resultsDiv, data.error || 'Grup bulunamadı');
        }
    } catch (error) {
        console.error('Group lookup error:', error);
        showError(resultsDiv, 'Grup verisi alınırken hata oluştu. Tekrar deneyin.');
    }
}

// Handle player search
async function handlePlayerSearch(event) {
    event.preventDefault();
    
    const playerName = document.getElementById('playerName').value;
    const resultsDiv = document.getElementById('playerResults');
    
    if (!playerName) {
        showError(resultsDiv, 'Lütfen bir oyuncu adı girin');
        return;
    }

    showLoading(resultsDiv);

    try {
        const response = await fetch(`api/player_search.php?username=${encodeURIComponent(playerName)}`);
        const text = await response.text();
        
        // Clean the response from PHP warnings
        const jsonStart = text.indexOf('{');
        const cleanText = jsonStart !== -1 ? text.substring(jsonStart) : text;
        
        const data = JSON.parse(cleanText);

        if (data.success) {
            displayPlayerResults(data, resultsDiv);
            saveToLocalStorage('lastPlayerName', playerName);
        } else {
            showError(resultsDiv, data.error || 'Oyuncu bulunamadı');
        }
    } catch (error) {
        console.error('Player search error:', error);
        showError(resultsDiv, 'Oyuncu verisi alınırken hata oluştu. Tekrar deneyin.');
    }
}

// Display group results
function displayGroupResults(data, container) {
    const { group, members } = data;
    
    let html = `
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-users"></i> ${escapeHtml(group.name)}</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Grup ID:</strong> ${group.id}<br>
                        <strong>Üye Sayısı:</strong> ${group.memberCount.toLocaleString()}<br>
                        <strong>Açık Grup:</strong> ${group.isPublic ? 'Evet' : 'Hayır'}
                    </div>
                    <div class="col-md-6">
                        <strong>Açıklama:</strong><br>
                        <small class="text-muted">${escapeHtml(group.description.substring(0, 100))}...</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <input type="text" id="memberFilter" class="form-control" placeholder="Üye ara..." onkeyup="filterMembers()">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm" id="membersTable">
                        <thead>
                            <tr>
                                <th>Kullanıcı Adı</th>
                                <th>Görünen Ad</th>
                                <th>Rütbe</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    if (members.length === 0) {
        html += '<tr><td colspan="4" class="text-center">Üye bulunamadı</td></tr>';
    } else {
        members.forEach(member => {
            html += `
                <tr>
                    <td>${escapeHtml(member.username)}</td>
                    <td>${escapeHtml(member.displayName)}</td>
                    <td><span class="badge bg-secondary">${escapeHtml(member.role)}</span></td>
                    <td>${member.rank}</td>
                </tr>
            `;
        });
    }

    html += `
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Gösterilen: ${members.length} üye
                </small>
            </div>
        </div>
    `;

    container.innerHTML = html;
    currentMembers = members;
}

// Display player results
function displayPlayerResults(data, container) {
    const { user, groups } = data;
    
    let html = `
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-user"></i> ${escapeHtml(user.username)}</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Kullanıcı ID:</strong> ${user.id}<br>
                        <strong>Görünen Ad:</strong> ${escapeHtml(user.displayName)}<br>
                        <strong>Oluşturma Tarihi:</strong> ${formatDate(user.created)}
                    </div>
                    <div class="col-md-6">
                        <strong>Açıklama:</strong><br>
                        <small class="text-muted">${escapeHtml(user.description.substring(0, 100))}...</small>
                    </div>
                </div>
                
                <h6><i class="fas fa-history"></i> Grup Geçmişi ve Mevcut Rütbeler</h6>
    `;

    if (groups.length === 0) {
        html += '<div class="alert alert-info">Bu oyuncu hiçbir gruba üye değil.</div>';
    } else {
        html += `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Grup Adı</th>
                            <th>Grup ID</th>
                            <th>Rütbe</th>
                            <th>Rank</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        groups.forEach(group => {
            html += `
                <tr>
                    <td>${escapeHtml(group.groupName)}</td>
                    <td>${group.groupId}</td>
                    <td><span class="badge bg-primary">${escapeHtml(group.role)}</span></td>
                    <td>${group.rank}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    }

    html += `
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Toplam grup üyeliği: ${groups.length}
                </small>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

// Filter members function
function filterMembers() {
    const filter = document.getElementById('memberFilter');
    const table = document.getElementById('membersTable');
    
    if (!filter || !table) return;
    
    const filterValue = filter.value.toLowerCase();
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let shouldShow = false;

        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(filterValue)) {
                shouldShow = true;
                break;
            }
        }

        rows[i].style.display = shouldShow ? '' : 'none';
    }
}

// Utility functions
function showLoading(container) {
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="loading"></div>
            <p class="mt-2 text-muted">Yükleniyor...</p>
        </div>
    `;
}

function showError(container, message) {
    container.innerHTML = `
        <div class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-triangle"></i> ${escapeHtml(message)}
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Local storage functions
function saveToLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
        console.error('Local storage error:', error);
    }
}

function loadFromLocalStorage(key) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    } catch (error) {
        console.error('Local storage error:', error);
        return null;
    }
}

function loadSavedData() {
    const lastGroupId = loadFromLocalStorage('lastGroupId');
    const lastPlayerName = loadFromLocalStorage('lastPlayerName');
    
    if (lastGroupId) {
        const groupIdInput = document.getElementById('groupId');
        if (groupIdInput) {
            groupIdInput.value = lastGroupId;
        }
    }
    
    if (lastPlayerName) {
        const playerNameInput = document.getElementById('playerName');
        if (playerNameInput) {
            playerNameInput.value = lastPlayerName;
        }
    }
}

// Auto-refresh functionality
function checkForUpdates() {
    if (currentGroupId) {
        // Silently refresh group data in background
        fetch(`api/group_lookup.php?groupId=${currentGroupId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.members.length !== currentMembers.length) {
                    // Show notification of changes
                    showNotification('Grup üyeleri güncellendi');
                }
            })
            .catch(error => {
                console.error('Auto-refresh error:', error);
            });
    }
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-info-circle"></i> ${escapeHtml(message)}
        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Export functions for global use
window.filterMembers = filterMembers;
window.showNotification = showNotification;
