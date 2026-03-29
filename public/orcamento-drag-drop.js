// Sistema unificado de Drag and Drop para grupos, categorias e itens
document.addEventListener('DOMContentLoaded', function() {
    let draggedElement = null;
    let draggedType = null; // 'item', 'category', ou 'group'
    
    const tbody = document.querySelector('#orcamento-table tbody');
    if (!tbody) return;
    
    tbody.addEventListener('dragover', e => e.preventDefault());
    tbody.addEventListener('drop', e => e.preventDefault());
    
    // Configurar drag para GRUPOS
    document.querySelectorAll('.group-row').forEach(row => {
        row.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedType = 'group';
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            console.log('🎯 Arrastando GRUPO:', this.dataset.grupo);
        });
        
        row.addEventListener('dragend', function(e) {
            this.style.opacity = '';
            clearHighlights();
        });
        
        row.addEventListener('dragover', function(e) {
            if (draggedType === 'group' && draggedElement !== this) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                clearHighlights();
                this.style.borderTop = e.clientY < midpoint ? '4px solid #4FC3F7' : '';
                this.style.borderBottom = e.clientY >= midpoint ? '4px solid #4FC3F7' : '';
            }
        });
        
        row.addEventListener('drop', function(e) {
            if (draggedType === 'group' && draggedElement !== this) {
                e.stopPropagation();
                console.log('📦 Soltando GRUPO');
                
                // Coletar TODOS os elementos do grupo (categorias, itens, totais)
                const elementsToMove = [draggedElement];
                let nextElement = draggedElement.nextElementSibling;
                while (nextElement && !nextElement.classList.contains('group-row')) {
                    elementsToMove.push(nextElement);
                    nextElement = nextElement.nextElementSibling;
                }
                
                console.log('   Movendo', elementsToMove.length, 'elementos');
                
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    elementsToMove.forEach(el => this.parentNode.insertBefore(el, this));
                } else {
                    let insertAfter = this.nextElementSibling;
                    while (insertAfter && !insertAfter.classList.contains('group-row')) {
                        insertAfter = insertAfter.nextElementSibling;
                    }
                    elementsToMove.forEach(el => {
                        if (insertAfter) {
                            this.parentNode.insertBefore(el, insertAfter);
                        } else {
                            this.parentNode.appendChild(el);
                        }
                    });
                }
                
                saveNewOrder();
            }
        });
    });
    
    // Configurar drag para CATEGORIAS
    document.querySelectorAll('.category-header').forEach(header => {
        header.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedType = 'category';
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            console.log('🎯 Arrastando CATEGORIA:', this.dataset.categoria);
        });
        
        header.addEventListener('dragend', function(e) {
            this.style.opacity = '';
            clearHighlights();
        });
        
        header.addEventListener('dragover', function(e) {
            e.preventDefault();
            
            if (draggedType === 'category' && draggedElement !== this) {
                e.dataTransfer.dropEffect = 'move';
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                clearHighlights();
                this.style.borderTop = e.clientY < midpoint ? '3px solid #2196F3' : '';
                this.style.borderBottom = e.clientY >= midpoint ? '3px solid #2196F3' : '';
            } else if (draggedType === 'item') {
                e.dataTransfer.dropEffect = 'move';
                this.style.backgroundColor = 'rgba(76, 175, 80, 0.2)';
            }
        });
        
        header.addEventListener('dragleave', function(e) {
            this.style.borderTop = '';
            this.style.borderBottom = '';
            this.style.backgroundColor = '';
        });
        
        header.addEventListener('drop', function(e) {
            e.stopPropagation();
            this.style.backgroundColor = '';
            
            if (draggedType === 'item') {
                console.log('📦 Soltando ITEM na categoria');
                // Item sendo solto na categoria - adicionar ao final
                let insertAfter = this;
                let nextElement = this.nextElementSibling;
                while (nextElement && nextElement.classList.contains('item-row')) {
                    insertAfter = nextElement;
                    nextElement = nextElement.nextElementSibling;
                }
                this.parentNode.insertBefore(draggedElement, insertAfter.nextSibling);
                saveNewOrder();
                
            } else if (draggedType === 'category' && draggedElement !== this) {
                console.log('📦 Soltando CATEGORIA');
                // Categoria sendo movida - mover com todos os itens e total
                const elementsToMove = [draggedElement];
                let nextElement = draggedElement.nextElementSibling;
                while (nextElement && (nextElement.classList.contains('item-row') || nextElement.classList.contains('total-row'))) {
                    elementsToMove.push(nextElement);
                    nextElement = nextElement.nextElementSibling;
                }
                
                console.log('   Movendo', elementsToMove.length, 'elementos');
                
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    elementsToMove.forEach(el => this.parentNode.insertBefore(el, this));
                } else {
                    let insertAfter = this.nextElementSibling;
                    while (insertAfter && (insertAfter.classList.contains('item-row') || insertAfter.classList.contains('total-row'))) {
                        insertAfter = insertAfter.nextElementSibling;
                    }
                    elementsToMove.forEach(el => {
                        if (insertAfter) {
                            this.parentNode.insertBefore(el, insertAfter);
                        } else {
                            this.parentNode.appendChild(el);
                        }
                    });
                }
                
                saveNewOrder();
            }
        });
    });
    
    // Configurar drag para ITENS
    document.querySelectorAll('.item-row').forEach(row => {
        row.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedType = 'item';
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            console.log('🎯 Arrastando ITEM:', this.dataset.itemId);
        });
        
        row.addEventListener('dragend', function(e) {
            this.style.opacity = '';
            clearHighlights();
        });
        
        row.addEventListener('dragover', function(e) {
            if (draggedType === 'item' && draggedElement !== this) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                clearHighlights();
                this.style.borderTop = e.clientY < midpoint ? '2px solid #4CAF50' : '';
                this.style.borderBottom = e.clientY >= midpoint ? '2px solid #4CAF50' : '';
            }
        });
        
        row.addEventListener('drop', function(e) {
            if (draggedType === 'item' && draggedElement !== this) {
                e.stopPropagation();
                console.log('📦 Soltando ITEM');
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    this.parentNode.insertBefore(draggedElement, this);
                } else {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                }
                
                saveNewOrder();
            }
        });
    });
    
    function clearHighlights() {
        document.querySelectorAll('.item-row, .category-header, .group-row').forEach(el => {
            el.style.borderTop = '';
            el.style.borderBottom = '';
            el.style.backgroundColor = '';
        });
    }
    
    function saveNewOrder() {
        const categories = [];
        document.querySelectorAll('.category-header').forEach((header, catIndex) => {
            const items = [];
            let nextElement = header.nextElementSibling;
            while (nextElement && nextElement.classList.contains('item-row')) {
                items.push({
                    id: parseInt(nextElement.dataset.itemId),
                    ordem: items.length + 1
                });
                nextElement = nextElement.nextElementSibling;
            }
            
            categories.push({
                categoria: header.dataset.categoria,
                grupo: header.dataset.grupo,
                ordem_categoria: catIndex + 1,
                items: items
            });
        });
        
        console.log('💾 Salvando nova ordem:', categories);
        
        const loadingMsg = document.createElement('div');
        loadingMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#2196F3;color:white;padding:12px 20px;border-radius:8px;z-index:9999;';
        loadingMsg.innerHTML = '⏳ Salvando...';
        document.body.appendChild(loadingMsg);
        
        const orcamentoId = document.querySelector('[data-orcamento-id]')?.dataset.orcamentoId || 
                            new URLSearchParams(window.location.search).get('id');
        
        fetch('/?route=orcamentos/reorderItems', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                orcamento_id: parseInt(orcamentoId),
                categories: categories
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadingMsg.innerHTML = '✓ Salvo!';
                loadingMsg.style.background = '#4CAF50';
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                loadingMsg.innerHTML = '✗ Erro';
                loadingMsg.style.background = '#f44336';
                setTimeout(() => loadingMsg.remove(), 3000);
                console.error('Erro:', data.error);
            }
        })
        .catch(err => {
            loadingMsg.innerHTML = '✗ Erro';
            loadingMsg.style.background = '#f44336';
            setTimeout(() => loadingMsg.remove(), 3000);
            console.error('Erro:', err);
        });
    }
});
