/**
 * 威宇影城 - 核心JavaScript功能
 * 功能：載入 header/footer、檢查登入狀態、UI控制
 */

class WeiYuCinema {
    constructor() {
        this.sessionData = null;
        this.init();
    }

    /**
     * 初始化系統
     */
    async init() {
        try {
            // 載入頁首和頁尾
            await this.loadTemplates();
            
            // 檢查登入狀態
            await this.checkLoginStatus();
            
            // 更新UI
            this.updateUI();
            
            // 綁定事件
            this.bindEvents();
            
            console.log('威宇影城系統初始化完成');
        } catch (error) {
            console.error('系統初始化失敗:', error);
        }
    }

    /**
     * 載入頁首和頁尾模板
     */
    async loadTemplates() {
        const promises = [];

        // 載入頁首
        const headerElement = document.getElementById('header-placeholder');
        if (headerElement) {
            promises.push(
                fetch('/WeiYuCinema/static/templates/header.html')
                    .then(response => response.text())
                    .then(html => {
                        headerElement.innerHTML = html;
                    })
            );
        }

        // 載入頁尾
        const footerElement = document.getElementById('footer-placeholder');
        if (footerElement) {
            promises.push(
                fetch('/WeiYuCinema/static/templates/footer.html')
                    .then(response => response.text())
                    .then(html => {
                        footerElement.innerHTML = html;
                    })
            );
        }

        await Promise.all(promises);
    }

    /**
     * 檢查登入狀態
     */
    async checkLoginStatus() {
        try {
            const response = await fetch('/WeiYuCinema/includes/session.php');
            const data = await response.json();
            
            this.sessionData = data;
            
            console.log('登入狀態:', data);
        } catch (error) {
            console.error('檢查登入狀態失敗:', error);
            this.sessionData = {
                isLoggedIn: false,
                role: null,
                memberName: null,
                memberId: null
            };
        }
    }

    /**
     * 更新UI根據登入狀態
     */
    updateUI() {
        if (!this.sessionData) return;

        const { isLoggedIn, role, memberName, memberId } = this.sessionData;

        // 隱藏所有導覽選單
        this.hideAllNavs();

        if (isLoggedIn) {
            // 顯示使用者資訊
            this.showUserInfo(memberName);
            
            // 根據角色顯示對應選單
            if (role === 1) {
                // 管理員
                this.showElement('admin-nav');
            } else {
                // 一般會員
                this.showElement('member-nav');
            }
        } else {
            // 未登入，顯示訪客選單
            this.showElement('guest-nav');
        }
    }

    /**
     * 隱藏所有導覽選單
     */
    hideAllNavs() {
        const navs = ['guest-nav', 'member-nav', 'admin-nav'];
        navs.forEach(navId => this.hideElement(navId));
        this.hideElement('user-welcome');
        this.hideElement('logout-link');
    }

    /**
     * 顯示使用者資訊
     */
    showUserInfo(memberName) {
        const userNameElement = document.getElementById('user-name');
        if (userNameElement) {
            userNameElement.textContent = memberName;
        }
        
        this.showElement('user-welcome');
        this.showElement('logout-link');
    }

    /**
     * 顯示元素
     */
    showElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('hidden');
        }
    }

    /**
     * 隱藏元素
     */
    hideElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add('hidden');
        }
    }

    /**
     * 綁定事件
     */
    bindEvents() {
        // 登出確認
        document.addEventListener('click', (e) => {
            if (e.target.id === 'logout-link') {
                e.preventDefault();
                this.confirmLogout();
            }
        });

        // 頁面載入動畫
        document.body.classList.add('fade-in');

        // 表單增強
        this.enhanceForms();
    }

    /**
     * 登出確認
     */
    confirmLogout() {
        if (confirm('確定要登出嗎？')) {
            window.location.href = '/WeiYuCinema/auth/logout.php';
        }
    }

    /**
     * 表單增強功能
     */
    enhanceForms() {
        // 為所有按鈕添加載入效果
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML += ' <span class="loading"></span>';
                    submitBtn.disabled = true;
                }
            });
        });

        // 輸入框焦點效果
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('glow');
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('glow');
            });
        });
    }

    /**
     * 顯示通知訊息
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // 添加樣式
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: 'bold',
            zIndex: '9999',
            animation: 'fadeIn 0.3s ease-in'
        });

        // 根據類型設置背景色
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        document.body.appendChild(notification);

        // 3秒後自動移除
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /**
     * 獲取當前登入狀態
     */
    getSessionData() {
        return this.sessionData;
    }
}

// 頁面載入完成後初始化
document.addEventListener('DOMContentLoaded', () => {
    window.weiYuCinema = new WeiYuCinema();
});

// 全域輔助函數
window.showNotification = (message, type) => {
    if (window.weiYuCinema) {
        window.weiYuCinema.showNotification(message, type);
    }
};

// 座位選擇功能增強
window.initSeatSelection = (ticketPrice) => {
    let selectedSeats = [];
    const maxSeats = 8;
    
    document.querySelectorAll('.seat.available').forEach(seat => {
        seat.addEventListener('click', function() {
            const seatId = this.dataset.seat;
            
            if (this.classList.contains('selected')) {
                // 取消選擇
                this.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== seatId);
            } else {
                // 檢查是否超過最大座位數
                if (selectedSeats.length >= maxSeats) {
                    showNotification('最多只能選擇 ' + maxSeats + ' 個座位', 'warning');
                    return;
                }
                
                // 選擇座位
                this.classList.add('selected');
                selectedSeats.push(seatId);
            }
            
            updateBookingSummary(selectedSeats, ticketPrice);
        });
    });
};

// 更新訂票摘要
function updateBookingSummary(selectedSeats, ticketPrice) {
    const ticketCount = selectedSeats.length;
    const totalPrice = ticketCount * ticketPrice;
    
    const selectedSeatsElement = document.getElementById('selectedSeats');
    const ticketCountElement = document.getElementById('ticketCount');
    const totalPriceElement = document.getElementById('totalPrice');
    
    if (selectedSeatsElement) {
        selectedSeatsElement.textContent = selectedSeats.length > 0 ? selectedSeats.sort().join(', ') : '尚未選擇';
    }
    
    if (ticketCountElement) {
        ticketCountElement.textContent = ticketCount;
    }
    
    if (totalPriceElement) {
        totalPriceElement.textContent = totalPrice.toLocaleString();
    }
    
    // 更新隱藏欄位
    const selectedSeatsInput = document.getElementById('selectedSeatsInput');
    const ticketCountInput = document.getElementById('ticketCountInput');
    const totalPriceInput = document.getElementById('totalPriceInput');
    
    if (selectedSeatsInput) selectedSeatsInput.value = selectedSeats.join(',');
    if (ticketCountInput) ticketCountInput.value = ticketCount;
    if (totalPriceInput) totalPriceInput.value = totalPrice;
    
    // 啟用/禁用下一步按鈕
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
        nextBtn.disabled = ticketCount === 0;
    }
}

// 餐點選擇功能增強
window.initMealSelection = (ticketTotalPrice) => {
    let selectedMeals = {};
    
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mealId = this.dataset.mealId;
            const input = document.querySelector(`.qty-input[data-meal-id="${mealId}"]`);
            let currentValue = parseInt(input.value);
            
            if (this.classList.contains('plus')) {
                if (currentValue < 10) {
                    input.value = currentValue + 1;
                }
            } else if (this.classList.contains('minus')) {
                if (currentValue > 0) {
                    input.value = currentValue - 1;
                }
            }
            
            updateMealSelection(ticketTotalPrice);
        });
    });
};

// 更新餐點選擇
function updateMealSelection(ticketTotalPrice) {
    let selectedMeals = {};
    let mealTotal = 0;
    
    document.querySelectorAll('.qty-input').forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const mealId = input.dataset.mealId;
            const mealName = input.dataset.mealName;
            const mealPrice = parseInt(input.dataset.mealPrice);
            
            selectedMeals[mealId] = {
                name: mealName,
                price: mealPrice,
                quantity: quantity,
                subtotal: mealPrice * quantity
            };
            
            mealTotal += mealPrice * quantity;
        }
    });
    
    const grandTotal = ticketTotalPrice + mealTotal;
    
    // 更新餐點列表顯示
    const selectedMealsDiv = document.getElementById('selectedMeals');
    if (selectedMealsDiv) {
        if (Object.keys(selectedMeals).length === 0) {
            selectedMealsDiv.innerHTML = '<div class="no-meals-message">尚未選擇餐點</div>';
        } else {
            let mealsHtml = '<ul>';
            for (const [mealId, meal] of Object.entries(selectedMeals)) {
                mealsHtml += `<li><strong>${meal.name}</strong> x ${meal.quantity} = NT$ ${meal.subtotal.toLocaleString()}</li>`;
            }
            mealsHtml += '</ul>';
            selectedMealsDiv.innerHTML = mealsHtml;
        }
    }
    
    // 更新金額
    const mealTotalElement = document.getElementById('mealTotal');
    const grandTotalElement = document.getElementById('grandTotal');
    
    if (mealTotalElement) mealTotalElement.textContent = mealTotal.toLocaleString();
    if (grandTotalElement) grandTotalElement.textContent = grandTotal.toLocaleString();
    
    // 更新隱藏欄位
    const selectedMealsInput = document.getElementById('selectedMealsInput');
    const mealTotalPriceInput = document.getElementById('mealTotalPriceInput');
    const grandTotalPriceInput = document.getElementById('grandTotalPriceInput');
    
    if (selectedMealsInput) selectedMealsInput.value = JSON.stringify(selectedMeals);
    if (mealTotalPriceInput) mealTotalPriceInput.value = mealTotal;
    if (grandTotalPriceInput) grandTotalPriceInput.value = grandTotal;
}
