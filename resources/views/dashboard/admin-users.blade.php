@extends('layouts.app')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    try {
        Alpine.data('userManagement', () => ({
        // Pricing context
        officialPrice: {{ is_numeric($defaultPrice ?? 0) ? (float)($defaultPrice ?? 0) : 0 }},

        deleteModalOpen: false,
        deleteUserId: null,
        deleteUserName: '',
        deleteUserEmail: '',
        deleteFormAction: '',
        viewDetailsModalOpen: false,
        viewDetailsLoading: false,
        viewDetailsData: null,
        editModalOpen: false,
        imageModal: {
            open: false,
            src: '',
            title: ''
        },
        editUserId: null,
        editUserName: '',
        editUserEmail: '',
        editUserPhone: '',
        editUserRole: '',
        editUserTokenBalance: 0,
        editUserCoinPrice: 0,
        editUserOriginalBalance: 0,
        editFormAction: '',
        editFormActionUpdateWallet: '',
        resetPasswordModalOpen: false,
        resetPasswordUserId: null,
        resetPasswordUserName: '',
        resetPasswordUserEmail: '',
        resetPasswordFormAction: '',
        createUserModalOpen: false,
        assignWalletLoading: false,
        assignWalletModalOpen: false,
        assignWalletUserId: null,
        assignWalletUserName: '',
        assignWalletFormAction: '',
        updateWalletLoading: false,
        confirmModalOpen: false,
        confirmModalTitle: '',
        confirmModalMessage: '',
        confirmModalAction: null,
        confirmModalActionText: 'Confirm',
        confirmModalType: 'warning', // warning, danger, info
        // List loading + toast
        isListLoading: false,
        toast: {
            visible: false,
            message: '',
            type: 'success' // success, error, warning, info
        },
        init() {
            // Initialize pagination handlers after DOM is ready
            this.$nextTick(() => {
                try {
                    this.initPaginationHandlers();
                } catch (e) {
                    console.warn('Pagination init error:', e);
                }
            });
        },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;
            setTimeout(() => {
                this.toast.visible = false;
            }, 3000);
        },
        // Admin Users – AJAX filters (no full page reload)
        async submitFilters(form) {
            if (!form) {
                console.error('submitFilters called without form element');
                return;
            }
            
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const baseUrl = (form && form.getAttribute) ? form.getAttribute('action') || '{{ route('admin.users') }}' : '{{ route('admin.users') }}';
            const url = baseUrl + (params ? ('?' + params) : '');

            this.isListLoading = true;
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#adminUsersTable');
                const current = document.querySelector('#adminUsersTable');

                if (incoming && current && current.innerHTML !== undefined) {
                    try {
                        current.innerHTML = incoming.innerHTML;
                    } catch (err) {
                        console.error('Error updating table content:', err);
                    }
                }

                // Update pagination section
                const incomingPagination = doc.querySelector('[data-pagination-section]');
                const currentPagination = document.querySelector('[data-pagination-section]');
                if (incomingPagination && currentPagination && currentPagination.innerHTML !== undefined) {
                    try {
                        currentPagination.innerHTML = incomingPagination.innerHTML;
                    } catch (err) {
                        console.error('Error updating pagination:', err);
                    }
                }

                // Update showing text
                const incomingShowing = doc.querySelector('[data-showing-text]');
                const currentShowing = document.querySelector('[data-showing-text]');
                if (incomingShowing && currentShowing && currentShowing.innerHTML !== undefined) {
                    try {
                        currentShowing.innerHTML = incomingShowing.innerHTML;
                    } catch (err) {
                        console.error('Error updating showing text:', err);
                    }
                }

                // Update sort buttons section to reflect new sort state
                const incomingSortSection = doc.querySelector('[data-sort-buttons]');
                const currentSortSection = document.querySelector('[data-sort-buttons]');
                if (incomingSortSection && currentSortSection && currentSortSection.innerHTML !== undefined) {
                    try {
                        currentSortSection.innerHTML = incomingSortSection.innerHTML;
                    } catch (err) {
                        console.error('Error updating sort buttons:', err);
                    }
                }

                if (window.history && window.history.replaceState) {
                    try {
                        window.history.replaceState({}, '', url);
                    } catch (err) {
                        console.error('Error updating URL:', err);
                    }
                }

                // Re-initialize pagination click handlers after content update
                this.initPaginationHandlers();
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load users. Please try again.', 'error');
            } finally {
                this.isListLoading = false;
            }
        },
        clearFilters() {
            const form = document.querySelector('[data-admin-users-filters]');
            if (!form) {
                console.warn('Filter form not found');
                return;
            }

            try {
                // Reset visible inputs
                if (form.reset && typeof form.reset === 'function') {
                    form.reset();
                }

                // Explicitly clear known query params
                ['q','role','sort','dir','page','per_page'].forEach((name) => {
                    try {
                        const field = form.querySelector(`[name="${name}"]`);
                        if (field && field.value !== undefined) {
                            field.value = '';
                        }
                    } catch (err) {
                        console.warn(`Error clearing field ${name}:`, err);
                    }
                });

                this.submitFilters(form);
            } catch (err) {
                console.error('Error clearing filters:', err);
                this.showToast('Error clearing filters. Please try again.', 'error');
            }
        },
        // Handle sort button clicks (AJAX, no page reload)
        async applySort(sortField) {
            const form = document.querySelector('[data-admin-users-filters]');
            if (!form) {
                console.error('Filter form not found');
                return;
            }

            // Get current sort and direction from URL params or form
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort') || '{{ request('sort', 'created_at') }}';
            const currentDir = urlParams.get('dir') || '{{ request('dir', 'desc') }}';
            
            // Determine new direction: if clicking same field, toggle; otherwise default to desc
            let newDir = 'desc';
            if (sortField === currentSort) {
                newDir = currentDir === 'asc' ? 'desc' : 'asc';
            }

            // Update or create hidden inputs for sort and dir
            let sortInput = form.querySelector('input[name="sort"]');
            let dirInput = form.querySelector('input[name="dir"]');
            
            if (!sortInput) {
                sortInput = document.createElement('input');
                sortInput.type = 'hidden';
                sortInput.name = 'sort';
                form.appendChild(sortInput);
            }
            
            if (!dirInput) {
                dirInput = document.createElement('input');
                dirInput.type = 'hidden';
                dirInput.name = 'dir';
                form.appendChild(dirInput);
            }
            
            sortInput.value = sortField;
            dirInput.value = newDir;

            // Remove page parameter to go to first page when sorting
            const pageInput = form.querySelector('input[name="page"]');
            if (pageInput) {
                pageInput.value = '1';
            }

            // Submit filters via AJAX
            await this.submitFilters(form);
        },
        async loadPage(url) {
            // Load a specific page via AJAX
            this.isListLoading = true;
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#adminUsersTable');
                const current = document.querySelector('#adminUsersTable');

                if (incoming && current && current.innerHTML !== undefined) {
                    try {
                        current.innerHTML = incoming.innerHTML;
                    } catch (err) {
                        console.error('Error updating table content:', err);
                    }
                }

                // Update pagination section
                const incomingPagination = doc.querySelector('[data-pagination-section]');
                const currentPagination = document.querySelector('[data-pagination-section]');
                if (incomingPagination && currentPagination && currentPagination.innerHTML !== undefined) {
                    try {
                        currentPagination.innerHTML = incomingPagination.innerHTML;
                    } catch (err) {
                        console.error('Error updating pagination:', err);
                    }
                }

                // Update showing text
                const incomingShowing = doc.querySelector('[data-showing-text]');
                const currentShowing = document.querySelector('[data-showing-text]');
                if (incomingShowing && currentShowing && currentShowing.innerHTML !== undefined) {
                    try {
                        currentShowing.innerHTML = incomingShowing.innerHTML;
                    } catch (err) {
                        console.error('Error updating showing text:', err);
                    }
                }

                // Update URL without reload
                if (window.history && window.history.pushState) {
                    try {
                        window.history.pushState({}, '', url);
                    } catch (err) {
                        console.error('Error updating URL:', err);
                    }
                }

                // Re-initialize pagination click handlers
                this.initPaginationHandlers();
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load page. Please try again.', 'error');
            } finally {
                this.isListLoading = false;
            }
        },
        initPaginationHandlers() {
            // Intercept all pagination link clicks
            try {
                const paginationLinks = document.querySelectorAll('[data-pagination-section] a[href]');
                if (!paginationLinks || paginationLinks.length === 0) {
                    return; // No pagination links found, exit early
                }
                
                paginationLinks.forEach(link => {
                    if (!link || !link.parentNode) {
                        return; // Skip if link or parent is null
                    }
                    
                    try {
                        // Remove existing listener to avoid duplicates
                        const newLink = link.cloneNode(true);
                        if (link.parentNode) {
                            link.parentNode.replaceChild(newLink, link);
                        }
                        
                        newLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            const url = newLink.getAttribute('href');
                            if (url) {
                                this.loadPage(url);
                            }
                        });
                    } catch (err) {
                        console.warn('Error initializing pagination link:', err);
                        // Continue with other links even if one fails
                    }
                });
            } catch (err) {
                console.warn('Error initializing pagination handlers:', err);
                // Fail silently - pagination will still work with default behavior
            }
        },
        openAssignWalletModal(userId, userName) {
            // Use ULID route
            this.assignWalletFormAction = '{{ url("/a/u") }}/' + encodeURIComponent(userId) + '/assign-wallet';
            this.assignWalletUserId = userId;
            this.assignWalletUserName = userName;
            this.assignWalletModalOpen = true;
        },
        closeAssignWalletModal() {
            this.assignWalletModalOpen = false;
            this.assignWalletUserId = null;
            this.assignWalletUserName = '';
        },
        async copyWalletAddress(address) {
            try {
                await navigator.clipboard.writeText(address);
                this.showToast('Wallet address copied to clipboard!', 'success');
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = address;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showToast('Wallet address copied to clipboard!', 'success');
            }
        },
        async confirmAssignWalletAddress() {
            if (!this.assignWalletUserId || !this.assignWalletFormAction) return;
            
            this.assignWalletLoading = true;
            try {
                const url = this.assignWalletFormAction;
                console.log('Assigning wallet address to user:', this.assignWalletUserId);
                console.log('Request URL:', url);
                
                // Get CSRF token
                const csrfToken = document.querySelector('input[name="_token"]')?.value || 
                                 document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                 '{{ csrf_token() }}';
                
                const formData = new FormData();
                formData.append('_token', csrfToken);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    this.showToast('Server returned an invalid response. Please check the console.', 'error');
                    this.assignWalletLoading = false;
                    return;
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (response.ok && data.success) {
                    this.showToast(data.message || 'Wallet address assigned successfully!', 'success');
                    this.closeAssignWalletModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    const errorMessage = data.message || data.error || 'Failed to assign wallet address';
                    console.error('Error response:', data);
                    this.showToast(errorMessage, 'error');
                }
            } catch (error) {
                console.error('Exception during wallet assignment:', error);
                this.showToast('Network error: ' + (error.message || 'Please check your connection and try again.'), 'error');
            } finally {
                this.assignWalletLoading = false;
            }
        },
        openEditModal(userId, userName, userEmail, userPhone, userRole, userTokenBalance) {
            this.editUserId = userId;
            this.editUserName = userName;
            this.editUserEmail = userEmail;
            this.editUserPhone = userPhone || '';
            this.editUserRole = userRole;
            this.editUserTokenBalance = userTokenBalance || 0;
            this.editUserOriginalBalance = userTokenBalance || 0;
            // Initialize coin price with current market price (can be updated by admin)
            this.editUserCoinPrice = {{ \App\Helpers\PriceHelper::getRwampPkrPrice() ?? 0 }};
            // Use ULID route
            this.editFormAction = '{{ url("/a/u") }}/' + encodeURIComponent(userId);
            this.editFormActionUpdateWallet = '{{ url("/a/u") }}/' + encodeURIComponent(userId) + '/assign-wallet';
            this.editModalOpen = true;
        },
        openResetPasswordModal(userId, userName, userEmail) {
            this.resetPasswordUserId = userId;
            this.resetPasswordUserName = userName;
            this.resetPasswordUserEmail = userEmail;
            this.resetPasswordFormAction = '{{ url("/a/u") }}/' + encodeURIComponent(userId) + '/reset-password';
            this.resetPasswordModalOpen = true;
        },
        openDeleteModal(userId, userName, userEmail) {
            this.deleteUserId = userId;
            this.deleteUserName = userName;
            this.deleteUserEmail = userEmail;
            this.deleteFormAction = '{{ url("/a/u") }}/' + encodeURIComponent(userId);
            this.deleteModalOpen = true;
        },
        async openViewDetailsModal(userId) {
            this.viewDetailsModalOpen = true;
            this.viewDetailsLoading = true;
            this.viewDetailsData = null;
            
            try {
                // Use ULID route
                const url = '{{ url("/a/u") }}/' + encodeURIComponent(userId) + '/details';
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', response.status, errorText);
                    throw new Error('Failed to fetch user details: ' + response.status);
                }
                
                const data = await response.json();
                
                if (!data || !data.user) {
                    throw new Error('Invalid response format');
                }
                
                this.viewDetailsData = data;
            } catch (error) {
                console.error('Error fetching user details:', error);
                this.showToast('Failed to load user details. Please try again.', 'error');
                // Keep modal open to show error state
            } finally {
                this.viewDetailsLoading = false;
            }
        },
        getUserName() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.name) || '';
        },
        getUserEmail() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.email) || '';
        },
        getUserPhone() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.phone) || '—';
        },
        getUserRole() {
            if (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.role) {
                const role = this.viewDetailsData.user.role;
                return role.charAt(0).toUpperCase() + role.slice(1);
            }
            return '—';
        },
        getUserCreatedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.created_at) || '—';
        },
        getUserEmailVerified() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.email_verified_at) || 'Not verified';
        },
        getTokenBalance() {
            const balance = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.token_balance) || 0;
            return balance.toLocaleString();
        },
        getTokenValue() {
            const price = Number(this.officialPrice || 0);
            const balance = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.token_balance) || 0;
            if (!price || !balance) {
                return (0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            const value = balance * price;
            return value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },
        getTokenValueNumeric() {
            const price = Number(this.officialPrice || 0);
            const balance = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.token_balance) || 0;
            if (!price || !balance) {
                return 0;
            }
            return balance * price;
        },
        getAverageBuyPrice() {
            const transactions = this.getTransactions();
            if (!transactions || !transactions.length) return null;

            let totalCoins = 0;
            let totalValue = 0;

            transactions.forEach(tx => {
                const amount = Number(tx.amount || 0);
                const pricePerCoin = tx.price_per_coin !== null && tx.price_per_coin !== undefined
                    ? Number(tx.price_per_coin)
                    : null;
                const totalPrice = tx.total_price !== null && tx.total_price !== undefined
                    ? Number(tx.total_price)
                    : null;

                // Consider only credit/positive amounts as purchases
                if (amount > 0 && (pricePerCoin || totalPrice)) {
                    const coins = Math.abs(amount);
                    totalCoins += coins;

                    if (!isNaN(totalPrice) && totalPrice > 0) {
                        totalValue += totalPrice;
                    } else if (!isNaN(pricePerCoin) && pricePerCoin > 0) {
                        totalValue += coins * pricePerCoin;
                    }
                }
            });

            if (!totalCoins || !totalValue) return null;

            return totalValue / totalCoins;
        },
        getValuePriceLine() {
            const official = Number(this.officialPrice || 0);
            const avgBuy = this.getAverageBuyPrice();

            if (official && avgBuy) {
                const officialLabel = official.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                const buyLabel = avgBuy.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                return `Official: Rs ${officialLabel} / token • Bought at: Rs ${buyLabel} / token`;
            }

            if (official) {
                const officialLabel = official.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                return `@ Rs ${officialLabel} per token`;
            }

            return 'No pricing data available';
        },
        getValuePriceLineHtml() {
            const official = Number(this.officialPrice || 0);
            const avgBuy = this.getAverageBuyPrice();

            // Check if formatPriceTag is available
            if (typeof window.formatPriceTag !== 'function') {
                // Fallback to plain text if formatPriceTag is not available
                if (official && avgBuy) {
                    const officialLabel = official.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const buyLabel = avgBuy.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    return `Official: Rs ${officialLabel} / token • Bought at: Rs ${buyLabel} / token`;
                }
                if (official) {
                    const officialLabel = official.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    return `Rs ${officialLabel} per token`;
                }
                return 'No pricing data available';
            }

            if (official && avgBuy) {
                const officialHtml = this.safeFormatPriceTag(official, {size: 'small', class: 'inline'});
                const buyHtml = this.safeFormatPriceTag(avgBuy, {size: 'small', class: 'inline'});
                return `Official: ${officialHtml} / token • Bought at: ${buyHtml} / token`;
            }

            if (official) {
                const officialHtml = this.safeFormatPriceTag(official, {size: 'small', class: 'inline'});
                return `${officialHtml} per token`;
            }

            return 'No pricing data available';
        },
        // Safe wrapper for formatPriceTag to prevent null reference errors
        safeFormatPriceTag(pkr, options = {}) {
            if (typeof window !== 'undefined' && typeof window.formatPriceTag === 'function') {
                return window.formatPriceTag(pkr, options);
            }
            // Fallback to plain number formatting if formatPriceTag is not available
            if (!pkr || pkr <= 0) return '<span class="text-gray-400">—</span>';
            const formatted = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(pkr);
            return `<span>PKR ${formatted}</span>`;
        },
        getWalletAddress() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.wallet_address) || 'Not set';
        },
        hasValidUserId() {
            try {
                if (!this.viewDetailsData || !this.viewDetailsData.user) return false;
                const user = this.viewDetailsData.user;
                return !!(user && (user.ulid || user.id));
            } catch (e) {
                console.error('hasValidUserId error:', e);
                return false;
            }
        },
        getSellCoinsUrl() {
            try {
                if (!this.viewDetailsData || !this.viewDetailsData.user) return '#';
                const user = this.viewDetailsData.user;
                const userId = user.ulid || user.id;
                if (!userId) return '#';
                return '{{ route('admin.sell') }}?user_id=' + encodeURIComponent(userId);
            } catch (e) {
                console.error('getSellCoinsUrl error:', e);
                return '#';
            }
        },
        getTransactionCount() {
            const count = (this.viewDetailsData && this.viewDetailsData.transactions && this.viewDetailsData.transactions.length) || 0;
            return '(' + count + ' transactions)';
        },
        hasTransactions() {
            return this.viewDetailsData && this.viewDetailsData.transactions && this.viewDetailsData.transactions.length > 0;
        },
        getTransactions() {
            try {
                if (!this.viewDetailsData || !this.viewDetailsData.transactions) {
                    return [];
                }
                // Ensure it's an array
                return Array.isArray(this.viewDetailsData.transactions) 
                    ? this.viewDetailsData.transactions 
                    : [];
            } catch (err) {
                console.warn('Error getting transactions:', err);
                return [];
            }
        },
        formatTransactionType(type) {
            if (!type) return '—';
            return type.replace('_', ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        },
        formatTransactionAmount(amount) {
            return (amount || 0).toLocaleString() + ' RWAMP';
        },
        getTransactionStatusClass(status) {
            if (status === 'completed') return 'bg-green-100 text-green-800';
            if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
            if (status === 'failed') return 'bg-red-100 text-red-800';
            return '';
        },
        formatTransactionStatus(status) {
            if (!status) return '—';
            return status.charAt(0).toUpperCase() + status.slice(1);
        },
        getKycStatus() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_status) || 'not_started';
        },
        getKycStatusBadge() {
            const status = this.getKycStatus();
            if (status === 'approved') return 'bg-green-100 text-green-800';
            if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
            if (status === 'rejected') return 'bg-red-100 text-red-800';
            return 'bg-gray-100 text-gray-800';
        },
        getKycStatusText() {
            const status = this.getKycStatus();
            if (status === 'approved') return 'Approved';
            if (status === 'pending') return 'Pending';
            if (status === 'rejected') return 'Rejected';
            return 'Not Started';
        },
        getKycIdType() {
            const type = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_type) || '';
            if (!type) return '—';
            return type.toUpperCase();
        },
        getKycIdNumber() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_number) || '—';
        },
        getKycFullName() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_full_name) || '—';
        },
        getKycSubmittedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_submitted_at) || '—';
        },
        getKycApprovedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_approved_at) || '—';
        },
        hasKycDocuments() {
            const user = this.viewDetailsData && this.viewDetailsData.user;
            return user && (user.kyc_id_front_path || user.kyc_id_back_path || user.kyc_selfie_path);
        },
        hasKycFront() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_front_path;
        },
        hasKycBack() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_back_path;
        },
        hasKycSelfie() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_selfie_path;
        },
        getKycFrontUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/front';
        },
        getKycBackUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/back';
        },
        getKycSelfieUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/selfie';
        },
        openKycImage(url, title) {
            if (!url || url === '#') {
                this.showCustomAlert('Invalid image URL. Please try again.', 'error');
                return;
            }
            // Set the image source and title
            this.imageModal.src = url;
            this.imageModal.title = title || 'KYC Document';
            // Open the modal
            this.imageModal.open = true;
            // Force a re-render by triggering Alpine
            this.$nextTick(() => {
                // Ensure the image loads
                const img = document.querySelector('[x-show="imageModal.open"] img');
                if (img) {
                    img.src = url;
                }
            });
        },
        closeKycImage() {
            this.imageModal.open = false;
            this.imageModal.src = '';
            this.imageModal.title = '';
        },
        closeViewDetailsModal() {
            this.viewDetailsModalOpen = false;
            this.viewDetailsData = null;
        },
        openConfirmModal(title, message, action, type = 'warning', actionText = 'Confirm') {
            this.confirmModalTitle = title;
            this.confirmModalMessage = message;
            this.confirmModalAction = action;
            this.confirmModalType = type;
            this.confirmModalActionText = actionText;
            this.confirmModalOpen = true;
        },
        closeConfirmModal() {
            this.confirmModalOpen = false;
            this.confirmModalTitle = '';
            this.confirmModalMessage = '';
            this.confirmModalAction = null;
            this.confirmModalActionText = 'Confirm';
            this.confirmModalType = 'warning';
        },
        confirmAction() {
            if (this.confirmModalAction && typeof this.confirmModalAction === 'function') {
                this.confirmModalAction();
            }
            this.closeConfirmModal();
        },
        async updateWalletAddress() {
            if (!this.editUserId || !this.editFormActionUpdateWallet) {
                this.showToast('Invalid user ID. Please try again.', 'error');
                return;
            }
            
            // Show custom confirmation modal
            this.openConfirmModal(
                'Update Wallet Address',
                'Are you sure you want to update the wallet address? This will generate a new wallet address for this user.',
                () => this.executeUpdateWalletAddress(),
                'warning',
                'Update Wallet'
            );
        },
        async executeUpdateWalletAddress() {
            this.updateWalletLoading = true;
            
            try {
                // Get CSRF token from form or meta tag
                const csrfToken = document.querySelector('input[name="_token"]')?.value || 
                                 document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                 '{{ csrf_token() }}';
                
                const formData = new FormData();
                formData.append('_token', csrfToken);
                
                const response = await fetch(this.editFormActionUpdateWallet, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message || 'Wallet address updated successfully!', 'success');
                    // Reload the page to show updated wallet address
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Failed to update wallet address.', 'error');
                }
            } catch (error) {
                console.error('Error updating wallet address:', error);
                this.showToast('Failed to update wallet address. Please try again.', 'error');
            } finally {
                this.updateWalletLoading = false;
            }
        },
        showCustomAlert(message, type = 'info') {
            this.openConfirmModal(
                type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Information',
                message,
                () => {},
                type,
                'OK'
            );
        }
    }));
    } catch (error) {
        console.error('Error initializing userManagement Alpine component:', error);
        // Provide a minimal fallback component to prevent complete failure
        Alpine.data('userManagement', () => ({
            officialPrice: 0,
            toast: { visible: false, message: '', type: 'success' },
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.visible = true;
                setTimeout(() => { this.toast.visible = false; }, 3000);
            },
            initPaginationHandlers() {
                // Empty fallback
            }
        }));
    }
});

// Calculate total price for create user form
function calculateCreateUserTotal() {
    const quantity = parseFloat(document.getElementById('createUserCoinQuantity')?.value || 0);
    const pricePerCoin = parseFloat(document.getElementById('createUserPricePerCoin')?.value || 0);
    const totalPriceDiv = document.getElementById('createUserTotalPrice');
    const totalPriceValue = document.getElementById('createUserTotalPriceValue');
    const totalCoins = document.getElementById('createUserTotalCoins');
    
    if (quantity > 0 && pricePerCoin > 0) {
        const totalPrice = quantity * pricePerCoin;
        totalPriceValue.textContent = 'PKR ' + totalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        totalCoins.textContent = quantity.toLocaleString() + ' RWAMP';
        totalPriceDiv.classList.remove('hidden');
    } else {
        totalPriceDiv.classList.add('hidden');
    }
}
</script>

<div class="min-h-screen bg-gray-50" x-data="userManagement">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">User Management</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Search, filter, and manage all users</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button 
                            @click="createUserModalOpen = true"
                            class="btn-primary flex items-center justify-center gap-2 text-sm px-4 py-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="whitespace-nowrap">Create New User</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Filters -->
        <form
            method="GET"
            action="{{ route('admin.users') }}"
            data-admin-users-filters
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4"
            @submit.prevent="submitFilters($event.target)"
        >
            <!-- Main Filter Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5 sm:gap-3 mb-3">
                <!-- Search -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <input 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Name, Email or Phone" 
                        class="w-full px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" 
                    />
            </div>
                
                <!-- Role -->
            <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors bg-white">
                    <option value="">All</option>
                    <option value="investor" @selected(request('role')==='investor')>Investor</option>
                    <option value="reseller" @selected(request('role')==='reseller')>Reseller</option>
                    <option value="admin" @selected(request('role')==='admin')>Admin</option>
                    <option value="user" @selected(request('role')==='user')>User</option>
                </select>
            </div>
                
                <!-- Rows/Page -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Rows/Page</label>
                    <select name="per_page" class="w-full px-2.5 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors bg-white" @change="submitFilters($event.target.closest('form'))">
                        <option value="10" @selected(request('per_page')==10 || (!request('per_page') && ($perPage ?? 15)==10))>10</option>
                        <option value="20" @selected(request('per_page')==20 || (!request('per_page') && ($perPage ?? 15)==20))>20</option>
                        <option value="50" @selected(request('per_page')==50 || (!request('per_page') && ($perPage ?? 15)==50))>50</option>
                        <option value="100" @selected(request('per_page')==100 || (!request('per_page') && ($perPage ?? 15)==100))>100</option>
                        <option value="15" @selected(request('per_page')==15 || (!request('per_page') && ($perPage ?? 15)==15))>15</option>
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="sm:col-span-2 lg:col-span-2 flex items-end gap-2">
                <button
                    type="submit"
                        class="flex-1 px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200 shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="isListLoading"
                >
                    <span x-show="!isListLoading">Apply</span>
                        <span x-show="isListLoading" class="flex items-center justify-center gap-1.5">
                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading…
                        </span>
                </button>
                <button
                    type="button"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors duration-200 shadow-sm hover:shadow"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>
            </div>
            
            <!-- Sort Options -->
            <div data-sort-buttons class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-200">
                <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Sort by:</label>
                @php
                    $currentSort = request('sort', 'created_at');
                    $currentDir = request('dir', 'desc');
                @endphp
                <button type="button" 
                        @click="applySort('created_at')"
                        class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $currentSort === 'created_at' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                    Date
                    @if($currentSort === 'created_at')
                        <span class="ml-1">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button type="button" 
                        @click="applySort('name')"
                        class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $currentSort === 'name' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                    Name
                    @if($currentSort === 'name')
                        <span class="ml-1">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button type="button" 
                        @click="applySort('email')"
                        class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $currentSort === 'email' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                    Email
                    @if($currentSort === 'email')
                        <span class="ml-1">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button type="button" 
                        @click="applySort('role')"
                        class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $currentSort === 'role' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                    Role
                    @if($currentSort === 'role')
                        <span class="ml-1">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button type="button" 
                        @click="applySort('token_balance')"
                        class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $currentSort === 'token_balance' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                    Balance
                    @if($currentSort === 'token_balance')
                        <span class="ml-1">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
            </div>
        </form>

        <!-- Users table -->
        <div id="adminUsersTable" class="bg-white rounded-xl shadow overflow-hidden">
            <!-- Total Users Count -->
            <div class="px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    <span class="font-semibold">Total Users:</span>
                    <span class="ml-2">{{ number_format($users->total()) }}</span>
                    @if($users->total() > 0)
                        <span class="text-gray-500 ml-2">
                            (Showing {{ $users->firstItem() }} - {{ $users->lastItem() }} of {{ $users->total() }})
                        </span>
                    @endif
                </div>
            </div>
            <div class="rw-table-scroll overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-xs sm:text-sm divide-y divide-gray-200 whitespace-nowrap">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-gray-600">
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden sm:table-cell">Email</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden md:table-cell">Phone</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">Role</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">Balance</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden md:table-cell">Wallet Address</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden lg:table-cell">Reseller</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden md:table-cell">Created</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
                                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500 sm:hidden">{{ $u->email }}</div>
                                @if($u->wallet_address)
                                    <div class="text-xs text-gray-500 sm:hidden mt-1">
                                        <span class="font-mono">Wallet: {{ $u->wallet_address }}</span>
                                        <button 
                                            @click="copyWalletAddress('{{ $u->wallet_address }}')"
                                            class="ml-1 inline-flex items-center px-1 py-0.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs"
                                            title="Copy">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="text-xs sm:hidden mt-1">
                                        <button 
                                            @click="openAssignWalletModal('{{ $u->ulid ?? $u->id }}', '{{ addslashes($u->name) }}')"
                                            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap w-full"
                                            style="background-color: #2563eb; color: #ffffff; border: none;"
                                            onmouseover="this.style.backgroundColor='#1d4ed8'"
                                            onmouseout="this.style.backgroundColor='#2563eb'"
                                            title="Assign wallet address">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span style="color: #ffffff; font-weight: 700; font-size: 12px;">Assign Wallet</span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">{{ $u->email }}</td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">{{ $u->phone ?? '—' }}</td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
                                <span class="rw-badge text-xs">{{ ucfirst($u->role ?? 'user') }}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
                                <span class="text-xs sm:text-sm font-semibold text-gray-900">
                                    {{ number_format($u->token_balance ?? 0, 0) }} RWAMP
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                                @if($u->wallet_address)
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs break-all">{{ $u->wallet_address }}</span>
                                        <button 
                                            @click="copyWalletAddress('{{ $u->wallet_address }}')"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow flex-shrink-0"
                                            title="Copy wallet address">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="hidden xl:inline">Copy</span>
                                        </button>
                                    </div>
                                @else
                                    <button 
                                        @click="openAssignWalletModal('{{ $u->ulid ?? $u->id }}', '{{ addslashes($u->name) }}')"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 shadow-md hover:shadow-lg whitespace-nowrap min-w-[100px]"
                                        style="background-color: #2563eb; color: #ffffff; border: none;"
                                        onmouseover="this.style.backgroundColor='#1d4ed8'"
                                        onmouseout="this.style.backgroundColor='#2563eb'"
                                        title="Assign wallet address">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span style="color: #ffffff; font-weight: 700; font-size: 13px;">Assign</span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell">
                                @if($u->reseller)
                                    <div class="text-xs">
                                        <div class="font-semibold text-blue-600">{{ $u->reseller->name }}</div>
                                        @if($u->reseller->referral_code)
                                            <div class="text-gray-500">{{ $u->reseller->referral_code }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">{{ $u->created_at?->format('Y-m-d') }}</td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm">
                                <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        @click="openEditModal('{{ $u->ulid ?? $u->id }}', '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->phone ?? '') }}', '{{ $u->role }}', {{ $u->token_balance ?? 0 }})"
                                        class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 sm:py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Edit</span>
                                    </button>
                                    
                                    <!-- Reset Password Button -->
                                    <button 
                                        @click="openResetPasswordModal('{{ $u->ulid ?? $u->id }}', '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                        class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Reset</span>
                                    </button>
                                    
                                    <!-- View Details Button -->
                                    <button 
                                        @click="openViewDetailsModal('{{ $u->ulid ?? $u->id }}')"
                                        class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 sm:py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">View</span>
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <button 
                                        @click="openDeleteModal('{{ $u->ulid ?? $u->id }}', '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="py-6 text-center text-gray-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200" data-pagination-section>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-700" data-showing-text>
                            Showing <span class="font-semibold">{{ $users->firstItem() }}</span> to 
                            <span class="font-semibold">{{ $users->lastItem() }}</span> of 
                            <span class="font-semibold">{{ $users->total() }}</span> users
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $users->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" 
         x-cloak
         @keydown.escape.window="editModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="editModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Edit User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Update user account information</p>
                                </div>
                            </div>
                            <button @click="editModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">📝 Note:</span> Update user information below. All changes will be saved immediately and the user will be notified of any modifications to their account.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Current User Information
                        </p>
                        <div class="grid md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="text-sm font-medium text-gray-900" x-text="editUserName"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p class="text-sm font-medium text-gray-900 break-all" x-text="editUserEmail"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div x-show="editUserId">
                        <form method="POST" x-bind:action="editFormAction" class="space-y-5">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        name="name" 
                                        x-model="editUserName" 
                                        class="rw-input w-full" 
                                        placeholder="Enter full name" 
                                        required 
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">The user's complete legal name</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        name="email" 
                                        type="email" 
                                        x-model="editUserEmail" 
                                        class="rw-input w-full" 
                                        placeholder="user@example.com" 
                                        required 
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">Valid email address for account access</p>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input 
                                    name="phone" 
                                    x-model="editUserPhone" 
                                    class="rw-input w-full" 
                                    placeholder="+1234567890" 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Contact number (optional)</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    User Role <span class="text-red-500">*</span>
                                </label>
                                <select name="role" x-model="editUserRole" class="rw-input w-full" required>
                                    <option value="user">User - Standard account access</option>
                                    <option value="investor">Investor - Investment features enabled</option>
                                    <option value="reseller">Reseller - Reseller program access</option>
                                    <option value="admin">Admin - Full system access</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">Select the appropriate role for this user</p>
                            </div>

                            <!-- Coin Quantity Update Section -->
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Coin Quantity <span class="text-gray-500 text-xs font-normal">(Current: <span x-text="editUserTokenBalance || 0"></span> coins)</span>
                                            </label>
                                            <input 
                                                name="token_balance" 
                                                type="number" 
                                                x-model="editUserTokenBalance"
                                                min="0" 
                                                step="0.01"
                                                class="rw-input w-full" 
                                                placeholder="Enter new coin quantity"
                                            />
                                            <p class="text-xs text-gray-500 mt-1.5">
                                                Update the user's token balance. Enter a value different from the current balance to create a transaction:
                                                <span class="block mt-1">
                                                    • <strong>Increase value:</strong> Credits coins to the user (purchase/credit transaction)
                                                    <br>
                                                    • <strong>Decrease value:</strong> Deducts coins from the user (sale/debit transaction)
                                                    <br>
                                                    • <strong>Keep same:</strong> No transaction will be created
                                                </span>
                                                <span class="text-red-600 font-semibold block mt-1" x-show="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) > 0.01">
                                                    Note: Price per coin is required when balance changes.
                                                </span>
                                            </p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Price Per Coin (PKR) <span class="text-red-500">*</span>
                                            </label>
                                            <input 
                                                name="price_per_coin" 
                                                type="number" 
                                                x-model="editUserCoinPrice"
                                                min="0" 
                                                step="0.01"
                                                class="rw-input w-full" 
                                                placeholder="Enter price per coin in PKR"
                                                :required="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) > 0.01"
                                            />
                                            <p class="text-xs text-gray-500 mt-1.5">
                                                Enter the price per coin at which the transaction will be recorded in history. 
                                                <span class="text-red-600 font-semibold" x-show="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) > 0.01">Required when coin balance changes.</span>
                                            </p>
                                        </div>
                                        
                                        <div x-show="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) > 0.01" class="mt-2 p-3 bg-white rounded-lg border border-blue-300">
                                            <p class="text-xs text-gray-700 font-medium">
                                                <span x-show="(editUserTokenBalance || 0) < (editUserOriginalBalance || 0)">
                                                    <span class="text-red-600 font-semibold">Sale Transaction:</span> 
                                                    <span x-text="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)).toFixed(2)"></span> coins will be sold at 
                                                    <span x-text="(editUserCoinPrice || 0).toFixed(2)"></span> PKR per coin 
                                                    (Total: <span x-text="(Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) * (editUserCoinPrice || 0)).toFixed(2)"></span> PKR)
                                                </span>
                                                <span x-show="(editUserTokenBalance || 0) > (editUserOriginalBalance || 0)">
                                                    <span class="text-green-600 font-semibold">Credit Transaction:</span> 
                                                    <span x-text="Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)).toFixed(2)"></span> coins will be credited at 
                                                    <span x-text="(editUserCoinPrice || 0).toFixed(2)"></span> PKR per coin 
                                                    (Total: <span x-text="(Math.abs((editUserTokenBalance || 0) - (editUserOriginalBalance || 0)) * (editUserCoinPrice || 0)).toFixed(2)"></span> PKR)
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guidelines Box -->
                            <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900">Important Guidelines</p>
                                        <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                            <li>Email changes require verification from the user</li>
                                            <li>Role changes affect user permissions immediately</li>
                                            <li>All changes are logged for security purposes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3 pt-4 border-t-2 border-gray-300">
                                <!-- Top Row: Wallet Update & Sell Coins -->
                                <div class="flex flex-wrap gap-3">
                                    <button 
                                        type="button"
                                        @click="updateWalletAddress()"
                                        :disabled="updateWalletLoading"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg x-show="!updateWalletLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <svg x-show="updateWalletLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="updateWalletLoading ? 'Updating...' : 'Update Wallet Address'"></span>
                                    </button>
                                <a 
                                    :href="'{{ route('admin.sell') }}?user_id=' + editUserId"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Sell Coins to User
                                </a>
                                </div>
                                
                                <!-- Bottom Row: Cancel & Save -->
                                <div class="flex justify-end gap-3">
                                    <button 
                                        @click="editModalOpen = false"
                                        type="button"
                                        class="btn-secondary px-6 py-2.5 text-sm">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel
                                        </span>
                                    </button>
                                    <button 
                                        type="submit" 
                                        class="btn-primary px-6 py-2.5 text-sm">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Save Changes
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="resetPasswordModalOpen" 
         x-cloak
         @keydown.escape.window="resetPasswordModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="resetPasswordModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="resetPasswordModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="resetPasswordModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Reset Password</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Change user account password</p>
                                </div>
                            </div>
                            <button @click="resetPasswordModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">🔐 Note:</span> Reset the password for this user account. You can set a custom password or use the default password. The user will be required to change their password on their next login for security.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User Account
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="resetPasswordUserName"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="resetPasswordUserEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div x-show="resetPasswordUserId">
                        <form method="POST" x-bind:action="resetPasswordFormAction" class="space-y-5">
                            @csrf
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    New Password
                                </label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    class="rw-input w-full" 
                                    placeholder="Enter custom password (min 8 characters)" 
                                    minlength="8"
                                />
                                <div class="mt-2 space-y-1">
                                    <p class="text-xs text-gray-500">
                                        <span class="font-medium">Option 1:</span> Enter a custom password (minimum 8 characters)
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <span class="font-medium">Option 2:</span> Leave empty to use default password: 
                                        <code class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">RWAMP@agent</code>
                                    </p>
                                </div>
                            </div>

                            <!-- Guidelines Box -->
                            <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900">What happens next?</p>
                                        <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                            <li>The user's password will be reset immediately</li>
                                            <li>An email notification will be sent to the user</li>
                                            <li>User must change password on next login (mandatory)</li>
                                            <li>Previous password will be invalidated</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div class="bg-primary/5 border-2 border-primary/30 p-3 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-xs text-gray-800 font-medium">
                                        <span class="font-bold text-primary">Security Note:</span> Always use strong passwords. If using default password, ensure the user changes it immediately upon login.
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                                <button 
                                    @click="resetPasswordModalOpen = false"
                                    type="button"
                                    class="btn-secondary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel
                                    </span>
                                </button>
                                <button 
                                    type="submit" 
                                    class="btn-primary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                        Reset Password
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         x-cloak
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="deleteModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-primary/20 to-primary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-primary/10"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/30">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/40 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Delete User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Permanently remove user account</p>
                                </div>
                            </div>
                            <button @click="deleteModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">⚠️ Warning:</span> You are about to permanently delete this user account from the system. This action will remove all user data, including account information, transaction history, and associated records.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User to be Deleted
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="deleteUserName"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="deleteUserEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Critical Warning Box -->
                    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-bold text-primary mb-2">⚠️ This action cannot be undone!</p>
                                <p class="text-xs text-gray-800 mb-3 font-medium">Deleting this user will permanently remove:</p>
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                    <li>User account and profile information</li>
                                    <li>All transaction and activity history</li>
                                    <li>Associated data and records</li>
                                    <li>Access to the system</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines Box -->
                    <div class="bg-accent/10 border-2 border-accent/30 p-4 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-gray-900 mb-1">Before deleting, consider:</p>
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                    <li>Is this user account truly inactive or unnecessary?</li>
                                    <li>Have you backed up any important data?</li>
                                    <li>Are there any pending transactions or obligations?</li>
                                    <li>Would deactivating the account be a better option?</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t-2 border-gray-300">
                    <button 
                        @click="deleteModalOpen = false"
                        type="button"
                        class="btn-secondary px-6 py-2.5 text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </span>
                    </button>
                    <form method="POST" x-bind:action="deleteFormAction" class="inline" x-show="deleteUserId">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            :disabled="!deleteUserId"
                            class="btn-primary px-6 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete User Permanently
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div x-show="viewDetailsModalOpen" 
         x-cloak
         @keydown.escape.window="closeViewDetailsModal()"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="viewDetailsModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeViewDetailsModal()"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="viewDetailsModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">User Details</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Balance, wallet, and transaction history</p>
                                </div>
                            </div>
                            <button @click="closeViewDetailsModal()" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white max-h-[70vh] overflow-y-auto">
                    <!-- Loading State -->
                    <div x-show="viewDetailsLoading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                        <p class="mt-4 text-gray-600">Loading user details...</p>
                    </div>

                    <!-- Error State -->
                    <div x-show="!viewDetailsLoading && !viewDetailsData && viewDetailsModalOpen" class="text-center py-12">
                        <div class="text-red-600 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-gray-900 mb-2">Failed to Load User Details</p>
                        <p class="text-sm text-gray-600 mb-4">Unable to fetch user information. Please try again.</p>
                        <button 
                            @click="closeViewDetailsModal()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
                            Close
                        </button>
                    </div>

                    <!-- User Details Content -->
                    <div x-show="!viewDetailsLoading && viewDetailsData" class="space-y-6">
                        <!-- User Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Account Information
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserName()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="getUserEmail()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Phone</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserPhone()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Role</p>
                                    <span class="rw-badge" x-text="getUserRole()"></span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Account Created</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserCreatedAt()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email Verified</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserEmailVerified()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Balance & Wallet -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-black text-white rounded-lg p-6">
                                <p class="text-xs text-white/70 mb-2">Token Balance</p>
                                <p class="text-3xl font-bold" x-text="getTokenBalance()"></p>
                                <p class="text-sm text-white/70 mt-2">RWAMP Tokens</p>
                            </div>
                            <div class="bg-accent text-black rounded-lg p-6">
                                <p class="text-xs text-black/70 mb-2">Value</p>
                                <div class="text-3xl font-bold" x-html="safeFormatPriceTag(getTokenValueNumeric() || 0, {size: 'large'})"></div>
                                <div class="text-sm text-black/70 mt-2" x-html="getValuePriceLineHtml()"></div>
                            </div>
                        </div>

                        <!-- Wallet Address -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Wallet Address
                            </h4>
                            <p class="font-mono text-sm break-all bg-white p-3 rounded border" x-text="getWalletAddress()"></p>
                        </div>

                        <!-- KYC Details -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                KYC Verification
                            </h4>
                            <div class="space-y-4">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">KYC Status</p>
                                        <span class="rw-badge" :class="getKycStatusBadge()" x-text="getKycStatusText()"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">ID Type</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycIdType()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">ID Number</p>
                                        <p class="text-sm font-semibold text-gray-900 font-mono" x-text="getKycIdNumber()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Full Name (on ID)</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycFullName()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Submitted At</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycSubmittedAt()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Approved At</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycApprovedAt()"></p>
                                    </div>
                                </div>
                                
                                <!-- KYC Documents -->
                                <div x-show="hasKycDocuments()" class="mt-4 pt-4 border-t border-gray-300">
                                    <p class="text-xs text-gray-500 mb-3">KYC Documents</p>
                                    <div class="flex gap-3">
                                        <button 
                                            x-show="hasKycFront()"
                                            @click="openKycImage(getKycFrontUrl(), 'ID Front - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Front
                                        </button>
                                        <button 
                                            x-show="hasKycBack()"
                                            @click="openKycImage(getKycBackUrl(), 'ID Back - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Back
                                        </button>
                                        <button 
                                            x-show="hasKycSelfie()"
                                            @click="openKycImage(getKycSelfieUrl(), 'Selfie - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Selfie
                                        </button>
                                    </div>
                                </div>
                                
                                <div x-show="!hasKycDocuments()" class="text-center py-4 text-gray-500 text-sm">
                                    <p>No KYC documents available</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Transaction History
                                <span class="text-sm font-normal text-gray-600" x-text="getTransactionCount()"></span>
                            </h4>
                            
                            <div x-show="!hasTransactions()" class="text-center py-8 text-gray-500">
                                <p>No transactions found</p>
                            </div>
                            
                            <div x-show="hasTransactions()" class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-gray-600 border-b">
                                            <th class="py-2 pr-4">Date</th>
                                            <th class="py-2 pr-4">Type</th>
                                            <th class="py-2 pr-4">Coins</th>
                                            <th class="py-2 pr-4">Price/Coin</th>
                                            <th class="py-2 pr-4">Total Price</th>
                                            <th class="py-2 pr-4">Status</th>
                                            <th class="py-2 pr-4">Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="transaction in getTransactions()" :key="transaction.id">
                                            <tr class="border-b">
                                                <td class="py-2 pr-4" x-text="transaction.created_at"></td>
                                                <td class="py-2 pr-4">
                                                    <span class="rw-badge" x-text="formatTransactionType(transaction.type)"></span>
                                                </td>
                                                <td class="py-2 pr-4 font-semibold" x-text="formatTransactionAmount(Math.abs(transaction.amount || 0))"></td>
                                                <td class="py-2 pr-4">
                                                    <span x-show="transaction.price_per_coin" x-html="safeFormatPriceTag(transaction.price_per_coin || 0, {size: 'small', class: 'inline'})"></span>
                                                    <span x-show="!transaction.price_per_coin" class="text-gray-400">—</span>
                                                </td>
                                                <td class="py-2 pr-4">
                                                    <span x-show="transaction.total_price" x-html="safeFormatPriceTag(transaction.total_price || 0, {size: 'small', class: 'inline'})"></span>
                                                    <span x-show="!transaction.total_price" class="text-gray-400">—</span>
                                                </td>
                                                <td class="py-2 pr-4">
                                                    <span class="rw-badge" 
                                                          :class="getTransactionStatusClass(transaction.status)"
                                                          x-text="formatTransactionStatus(transaction.status)"></span>
                                                </td>
                                                <td class="py-2 pr-4 font-mono text-xs break-all" x-text="transaction.reference || '—'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t-2 border-gray-300">
                    <template x-if="hasValidUserId()">
                        <a 
                            x-bind:href="getSellCoinsUrl()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Sell Coins to User
                        </a>
                    </template>
                    <button 
                        @click="closeViewDetailsModal()"
                        type="button"
                        class="btn-primary px-6 py-2.5 text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Close
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Image Viewer Modal -->
    <div x-show="imageModal.open" 
         x-cloak
         style="display: none; z-index: 9999;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-95 p-4"
         @click.self="closeKycImage()"
         @keydown.escape.window="closeKycImage()">
        <div class="relative max-w-6xl max-h-full w-full h-full flex flex-col bg-gray-900 rounded-lg shadow-2xl overflow-hidden" style="z-index: 10000;">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gray-900 text-white px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-montserrat font-bold" x-text="imageModal.title"></h3>
                <button 
                    @click="closeKycImage()" 
                    type="button"
                    class="text-white hover:text-red-400 hover:bg-red-500/20 transition-all duration-200 p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                    title="Close (ESC)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="flex-1 overflow-auto bg-gray-800 flex items-center justify-center p-4">
                <img :src="imageModal.src" 
                     :alt="imageModal.title"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                     x-on:error="$el.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage not found%3C/text%3E%3C/svg%3E'"
                     loading="lazy">
            </div>
            
            <!-- Footer with Download Option -->
            <div class="bg-gray-900 text-white px-6 py-3 border-t border-gray-700 flex items-center justify-between">
                <span class="text-sm text-gray-400">Click outside or press ESC to close</span>
                <a :href="imageModal.src" 
                   target="_blank"
                   class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- Create New User Modal -->
    <div x-show="createUserModalOpen" 
         x-cloak
         @keydown.escape.window="createUserModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createUserModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="createUserModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="createUserModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Create New User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Add a new user account to the system</p>
                                </div>
                            </div>
                            <button @click="createUserModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">➕ Note:</span> Fill in the form below to create a new user account. The user will receive login credentials via email and will be required to change their password on first login.
                        </p>
                    </div>

                    <!-- Form Section -->
                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                        @csrf
                        
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="name" 
                                    type="text"
                                    value="{{ old('name') }}"
                                    class="rw-input w-full" 
                                    placeholder="Enter full name" 
                                    required 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">The user's complete legal name</p>
                                @error('name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="email" 
                                    type="email" 
                                    value="{{ old('email') }}"
                                    class="rw-input w-full" 
                                    placeholder="user@example.com" 
                                    required 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Valid email address for account access</p>
                                @error('email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input 
                                    name="phone" 
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    class="rw-input w-full" 
                                    placeholder="+1234567890" 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Contact number (optional)</p>
                                @error('phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    User Role <span class="text-red-500">*</span>
                                </label>
                                <select name="role" class="rw-input w-full" required>
                                    <option value="">Select Role</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User - Standard account access</option>
                                    <option value="investor" {{ old('role') == 'investor' ? 'selected' : '' }}>Investor - Investment features enabled</option>
                                    <option value="reseller" {{ old('role') == 'reseller' ? 'selected' : '' }}>Reseller - Reseller program access</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin - Full system access</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">Select the appropriate role for this user</p>
                                @error('role')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <input 
                                name="password" 
                                type="password" 
                                class="rw-input w-full" 
                                placeholder="Enter password (min 8 characters)" 
                                minlength="8"
                            />
                            <div class="mt-2 space-y-1">
                                <p class="text-xs text-gray-500">
                                    <span class="font-medium">Option 1:</span> Enter a custom password (minimum 8 characters)
                                </p>
                                <p class="text-xs text-gray-500">
                                    <span class="font-medium">Option 2:</span> Leave empty to use default password: 
                                    <code class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">RWAMP@agent</code>
                                </p>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Coin Assignment Section -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Optional: Assign Coins to User
                            </h4>
                            <p class="text-xs text-blue-700 mb-4">You can optionally assign coins to this user during account creation. This will be recorded as a transaction.</p>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Coin Quantity (RWAMP)
                                    </label>
                                    <input 
                                        name="coin_quantity" 
                                        type="number" 
                                        value="{{ old('coin_quantity') }}"
                                        class="rw-input w-full" 
                                        placeholder="Enter quantity (optional)"
                                        min="0"
                                        step="1"
                                        id="createUserCoinQuantity"
                                        oninput="calculateCreateUserTotal()"
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">Number of RWAMP tokens to assign</p>
                                    @error('coin_quantity')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Price per Coin (PKR)
                                    </label>
                                    <input 
                                        name="price_per_coin" 
                                        type="number" 
                                        value="{{ old('price_per_coin', $defaultPrice ?? 3) }}"
                                        class="rw-input w-full" 
                                        placeholder="Enter price per coin"
                                        min="0.01"
                                        step="0.01"
                                        id="createUserPricePerCoin"
                                        oninput="calculateCreateUserTotal()"
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">Price per RWAMP token in PKR</p>
                                    @error('price_per_coin')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div id="createUserTotalPrice" class="hidden mt-4 p-3 bg-white border border-blue-300 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-600">Total Price:</p>
                                        <p class="text-lg font-bold text-blue-900" id="createUserTotalPriceValue">PKR 0.00</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600">Coins:</p>
                                        <p class="text-sm font-semibold text-blue-800" id="createUserTotalCoins">0 RWAMP</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guidelines Box -->
                        <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-gray-900">Important Information</p>
                                    <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                        <li>User will receive an email with login credentials</li>
                                        <li>User must change password on first login (mandatory)</li>
                                        <li>Email verification is required for account activation</li>
                                        <li>All user data will be logged for security purposes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                            <button 
                                @click="createUserModalOpen = false"
                                type="button"
                                class="btn-secondary px-6 py-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                </span>
                            </button>
                            <button 
                                type="submit" 
                                class="btn-primary px-6 py-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create User
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.visible" 
         x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-full"
         class="fixed top-4 right-4 z-[60] max-w-sm w-full"
         style="display: none;">
        <div :class="{
            'bg-white border-l-4 border-green-500': toast.type === 'success',
            'bg-white border-l-4 border-red-500': toast.type === 'error',
            'bg-white border-l-4 border-yellow-500': toast.type === 'warning',
            'bg-white border-l-4 border-blue-500': toast.type === 'info'
        }" class="rounded-lg shadow-lg p-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="mr-3">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p x-text="toast.message" class="text-sm font-medium text-gray-900"></p>
            </div>
            <button @click="toast.visible = false" class="ml-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Assign Wallet Address Confirmation Modal -->
    <div x-show="assignWalletModalOpen" 
         x-cloak
         @keydown.escape.window="closeAssignWalletModal()"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="assignWalletModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeAssignWalletModal()"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="assignWalletModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center shadow-xl ring-4 ring-purple-500/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Assign Wallet Address</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Generate wallet address for user</p>
                                </div>
                            </div>
                            <button @click="closeAssignWalletModal()" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-purple-50 border-l-4 border-purple-500 rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-purple-600 font-bold">💳 Note:</span> A unique 16-digit wallet address will be automatically generated and assigned to this user. This action cannot be undone.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User Information
                        </p>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="text-sm font-medium text-gray-900" x-text="assignWalletUserName"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                        <button 
                            @click="closeAssignWalletModal()"
                            type="button"
                            :disabled="assignWalletLoading"
                            class="btn-secondary px-6 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </span>
                        </button>
                        <button 
                            @click="confirmAssignWalletAddress()"
                            :disabled="assignWalletLoading"
                            class="btn-primary px-6 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed bg-purple-500 hover:bg-purple-600">
                            <span class="flex items-center gap-2">
                                <svg x-show="!assignWalletLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <svg x-show="assignWalletLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="assignWalletLoading ? 'Assigning...' : 'Assign Wallet'"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div x-show="confirmModalOpen" 
         x-cloak
         @keydown.escape.window="closeConfirmModal()"
         class="fixed inset-0 z-50 overflow-y-auto p-4"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="confirmModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeConfirmModal()"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="confirmModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-w-full border-4 border-primary relative z-10">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-6 py-5 border-b-4 border-primary">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center shadow-xl"
                                 :class="{
                                     'bg-yellow-500': confirmModalType === 'warning',
                                     'bg-red-600': confirmModalType === 'danger',
                                     'bg-blue-600': confirmModalType === 'info'
                                 }">
                                <svg x-show="confirmModalType === 'warning'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <svg x-show="confirmModalType === 'danger'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg x-show="confirmModalType === 'info'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-montserrat font-bold text-white tracking-tight" x-text="confirmModalTitle"></h3>
                        </div>
                        <button @click="closeConfirmModal()" class="text-white/90 hover:text-white transition-all duration-200 p-2 hover:bg-white/20 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6 bg-white">
                    <p class="text-gray-700 text-sm leading-relaxed" x-text="confirmModalMessage"></p>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <button 
                        @click="closeConfirmModal()"
                        type="button"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 shadow-sm hover:shadow">
                        Cancel
                    </button>
                    <button 
                        @click="confirmAction()"
                        type="button"
                        class="px-5 py-2.5 text-sm font-bold rounded-lg transition-colors duration-200 shadow-sm hover:shadow"
                        :style="{
                            color: '#ffffff',
                            backgroundColor: confirmModalType === 'warning' ? '#d97706' : confirmModalType === 'danger' ? '#dc2626' : '#2563eb',
                            fontWeight: '700'
                        }"
                        :class="{
                            'hover:bg-yellow-700': confirmModalType === 'warning',
                            'hover:bg-red-700': confirmModalType === 'danger',
                            'hover:bg-blue-700': confirmModalType === 'info'
                        }">
                        <span x-text="confirmModalActionText" style="color: #ffffff !important; font-weight: 700 !important;"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection


