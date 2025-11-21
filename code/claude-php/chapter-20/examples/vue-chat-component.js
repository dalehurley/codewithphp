const { createApp } = Vue;

createApp({
    data() {
        return {
            ws: null,
            isConnected: false,
            messages: [],
            newMessage: '',
            isTyping: false,
            currentAssistantMessage: '',
            userId: this.generateUserId(),
            wsUrl: 'ws://localhost:8080'
        };
    },

    computed: {
        connectionStatus() {
            return this.isConnected ? 'connected' : 'disconnected';
        }
    },

    mounted() {
        this.connect();
    },

    beforeUnmount() {
        if (this.ws) {
            this.ws.close();
        }
    },

    methods: {
        generateUserId() {
            return 'user_' + Math.random().toString(36).substr(2, 9);
        },

        connect() {
            try {
                this.ws = new WebSocket(this.wsUrl);

                this.ws.onopen = () => {
                    console.log('WebSocket connected');
                    this.isConnected = true;

                    // Authenticate
                    this.ws.send(JSON.stringify({
                        type: 'auth',
                        user_id: this.userId,
                        username: 'User ' + this.userId.substr(-4)
                    }));
                };

                this.ws.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                };

                this.ws.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    this.addSystemMessage('Connection error occurred');
                };

                this.ws.onclose = () => {
                    console.log('WebSocket disconnected');
                    this.isConnected = false;
                    this.addSystemMessage('Disconnected from server');
                };

            } catch (error) {
                console.error('Connection failed:', error);
                this.addSystemMessage('Failed to connect to server');
            }
        },

        disconnect() {
            if (this.ws) {
                this.ws.close();
                this.ws = null;
            }
        },

        toggleConnection() {
            if (this.isConnected) {
                this.disconnect();
            } else {
                this.connect();
            }
        },

        handleMessage(data) {
            console.log('Received:', data.type, data);

            switch (data.type) {
                case 'system':
                case 'auth_success':
                    this.addSystemMessage(data.message || 'Authenticated successfully');
                    break;

                case 'user_message':
                    // User message echo (already added locally)
                    break;

                case 'assistant_typing':
                    this.isTyping = data.is_typing;
                    if (data.is_typing) {
                        this.currentAssistantMessage = '';
                    }
                    break;

                case 'assistant_chunk':
                    this.currentAssistantMessage += data.chunk;
                    this.updateLastAssistantMessage(this.currentAssistantMessage);
                    break;

                case 'assistant_complete':
                    this.isTyping = false;
                    break;

                case 'history_cleared':
                    this.messages = [];
                    this.addSystemMessage('Conversation history cleared');
                    break;

                case 'error':
                    this.addSystemMessage('Error: ' + data.error);
                    this.isTyping = false;
                    break;

                default:
                    console.log('Unknown message type:', data.type);
            }
        },

        sendMessage() {
            if (!this.newMessage.trim() || !this.isConnected || this.isTyping) {
                return;
            }

            const message = this.newMessage.trim();

            // Add user message to UI
            this.messages.push({
                type: 'user',
                content: message,
                timestamp: Date.now()
            });

            // Send to server
            this.ws.send(JSON.stringify({
                type: 'message',
                message: message,
                system_prompt: 'You are a helpful AI assistant. Provide clear, concise responses.'
            }));

            this.newMessage = '';
            this.scrollToBottom();
        },

        updateLastAssistantMessage(content) {
            // Find the last assistant message or create new one
            const lastMessage = this.messages[this.messages.length - 1];

            if (lastMessage && lastMessage.type === 'assistant') {
                // Update existing message
                lastMessage.content = content;
            } else {
                // Create new assistant message
                this.messages.push({
                    type: 'assistant',
                    content: content,
                    timestamp: Date.now()
                });
            }

            this.scrollToBottom();
        },

        addSystemMessage(content) {
            this.messages.push({
                type: 'system',
                content: content,
                timestamp: Date.now()
            });
            this.scrollToBottom();
        },

        clearHistory() {
            if (!this.isConnected) {
                return;
            }

            this.ws.send(JSON.stringify({
                type: 'clear'
            }));
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
    }
}).mount('#app');
