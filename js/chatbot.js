/**
 * Chatbot Client-side Handler
 * Manages UI interactions and API communication
 */

class ChatbotManager {
    constructor() {
        this.container = document.getElementById('chatbot-container');
        this.toggle = document.getElementById('chatbot-toggle');
        this.closeBtn = document.getElementById('chatbot-close');
        this.messagesDiv = document.getElementById('chatbot-messages');
        this.input = document.getElementById('chatbot-input');
        this.sendBtn = document.getElementById('chatbot-send');
        
        this.isOpen = false;
        this.isSending = false;
        
        this.initializeEventListeners();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        this.toggle.addEventListener('click', () => this.toggleChat());
        this.closeBtn.addEventListener('click', () => this.closeChat());
        this.sendBtn.addEventListener('click', () => this.sendMessage());
        this.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Focus on input when container opens
        this.container.addEventListener('animationend', () => {
            if (this.isOpen) {
                this.input.focus();
            }
        });
    }

    /**
     * Toggle chat window open/close
     */
    toggleChat() {
        this.isOpen ? this.closeChat() : this.openChat();
    }

    /**
     * Open chat window
     */
    openChat() {
        this.isOpen = true;
        this.container.classList.add('active');
        this.toggle.style.display = 'none';
        this.input.focus();
    }

    /**
     * Close chat window
     */
    closeChat() {
        this.isOpen = false;
        this.container.classList.remove('active');
        this.toggle.style.display = 'flex';
    }

    /**
     * Send a message to the chatbot
     */
    async sendMessage() {
        const message = this.input.value.trim();
        
        if (!message) return;
        if (this.isSending) return;

        // Add user message to chat
        this.addMessage(message, 'user');
        this.input.value = '';
        this.isSending = true;
        this.sendBtn.disabled = true;

        // Show typing indicator
        this.addMessage('', 'bot', true);

        try {
            const response = await fetch('php/chatbot_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            // Remove typing indicator
            this.removeTypingIndicator();

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to get response from chatbot');
            }

            const data = await response.json();
            
            if (data.success) {
                this.addMessage(data.message, 'bot');
            } else {
                this.addMessage('Error: ' + (data.error || 'Unable to process your request'), 'bot');
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            this.removeTypingIndicator();
            this.addMessage(
                'Sorry, I encountered an error: ' + error.message + '. Please try again later.',
                'bot'
            );
        } finally {
            this.isSending = false;
            this.sendBtn.disabled = false;
            this.input.focus();
        }
    }

    /**
     * Add a message to the chat display
     * @param {string} text - The message text
     * @param {string} sender - 'user' or 'bot'
     * @param {boolean} isTyping - Whether to show typing indicator
     */
    addMessage(text, sender, isTyping = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        if (isTyping) {
            messageDiv.classList.add('typing');
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = `
                <div class="typing-indicator">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            `;
            messageDiv.appendChild(contentDiv);
        } else {
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = text;
            messageDiv.appendChild(contentDiv);
        }

        this.messagesDiv.appendChild(messageDiv);
        
        // Auto-scroll to bottom
        setTimeout(() => {
            this.messagesDiv.scrollTop = this.messagesDiv.scrollHeight;
        }, 0);

        return messageDiv;
    }

    /**
     * Remove the typing indicator
     */
    removeTypingIndicator() {
        const typingMessage = this.messagesDiv.querySelector('.message.bot.typing');
        if (typingMessage) {
            typingMessage.remove();
        }
    }
}

/**
 * Initialize chatbot when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('chatbot-toggle')) {
        window.chatbot = new ChatbotManager();
    }
});
