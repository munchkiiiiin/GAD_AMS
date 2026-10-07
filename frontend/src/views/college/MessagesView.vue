<template>
  <main class="messages-main-content">
    <div class="messages-content-wrapper">
      <CommunicationsHeader activeTab="messages" />
      
      <div class="info-note" v-if="isTWG" style="margin: 0 0 1.5rem 0; padding: 1rem 1.5rem; background-color: #1e293b; border: 1px solid rgba(147, 51, 234, 0.3); border-left: 4px solid #9333ea; border-radius: 0.75rem; display: flex; align-items: flex-start; gap: 1rem;">
        <div style="background: rgba(147, 51, 234, 0.2); padding: 0.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
          <span class="material-symbols-outlined" style="color: #c084fc; font-size: 1.2rem;">info</span>
        </div>
        <div>
          <h4 style="margin: 0 0 0.25rem 0; color: #f8fafc; font-size: 1rem; font-weight: 600;">Note</h4>
          <p style="margin: 0; color: #cbd5e1; font-size: 0.9rem; line-height: 1.4;">For document inquiries, it's recommended to send a message to the <strong style="color: #ffffff;">Director</strong>. For general concerns, it's recommended to send a message to the <strong style="color: #ffffff;">Staff</strong>.</p>
        </div>
      </div>

      <!-- Main Messenger Layout -->
      <div class="messenger-container">
        <!-- Sidebar -->
        <div class="messenger-sidebar" :class="{ 'hidden-on-mobile': rightPaneMode !== 'none' }">
           <div class="sidebar-top">
              <div class="sidebar-header">
                 <h2>Chats</h2>
                 <div class="sidebar-actions">
                     <button @click="toggleSelectionMode" class="icon-btn" :class="{'active-selection': isSelectionMode}" title="Select Multiple">
                        <span class="material-symbols-outlined">checklist</span>
                     </button>
                    <button @click="fetchMessages" class="icon-btn" title="Refresh">
                       <span class="material-symbols-outlined">refresh</span>
                    </button>
                    <button v-if="hasAnnouncementFeature" @click="rightPaneMode = 'announce'" class="icon-btn" title="Make Announcement">
                       <span class="material-symbols-outlined">campaign</span>
                    </button>
                    <button @click="rightPaneMode = 'compose'" class="icon-btn primary-icon-btn" title="New Message">
                       <span class="material-symbols-outlined">edit_square</span>
                    </button>
                 </div>
              </div>
              <div class="sidebar-tabs">
                 <button :class="{active: activeTab === 'chats'}" @click="activeTab = 'chats'">All Chats</button>
                 <button :class="{active: activeTab === 'trash'}" @click="activeTab = 'trash'">Trash</button>
              </div>
           </div>
           
           <div class="conversation-list">
              <div v-if="loadingMessages" class="loading-state">
                 <div class="spinner"></div>
              </div>
              <div v-else-if="filteredConversations.length === 0" class="empty-state">
                 <span class="material-symbols-outlined">{{ activeTab === 'trash' ? 'delete' : 'forum' }}</span>
                 <p>No conversations found</p>
              </div>
              <div v-else 
                   v-for="conv in filteredConversations" 
                   :key="conv.thread_id" 
                   class="conversation-item" 
                   :class="{active: activeConversationId === conv.thread_id && rightPaneMode === 'chat', selected: selectedThreads.includes(conv.thread_id)}"
                   @click="selectConversation(conv)">
                 <div v-if="isSelectionMode" class="conv-checkbox">
                    <input type="checkbox" :checked="selectedThreads.includes(conv.thread_id)" @click="toggleThreadSelection(conv.thread_id, $event)">
                 </div>
                 <div class="conv-avatar">
                    <span class="material-symbols-outlined">person</span>
                    <div v-if="conv.unread_count > 0 && activeTab !== 'trash'" class="unread-badge"></div>
                 </div>
                 <div class="conv-details">
                    <div class="conv-header">
                       <span class="conv-name" :class="{unread: conv.unread_count > 0 && activeTab !== 'trash'}">{{ conv.other_party }}</span>
                       <span class="conv-time">{{ conv.latest_time_short }}</span>
                    </div>
                    <div class="conv-role">{{ formatRole(conv.role) }} &bull; {{ getOfficeName(conv.office_id) }}</div>
                    <div class="conv-preview" :class="{unread: conv.unread_count > 0 && activeTab !== 'trash'}">
                       <span v-if="conv.is_announcement" class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; color: #facc15;">campaign</span>
                       {{ conv.preview }}
                    </div>
                 </div>
               </div>
            </div>
            
            <div v-if="isSelectionMode" class="bulk-action-toolbar">
              <span class="selected-count">{{ selectedThreads.length }} selected</span>
              <div class="bulk-actions">
                 <button v-if="activeTab === 'chats'" @click="bulkTrash" :disabled="selectedThreads.length === 0" class="bulk-btn trash-btn">Trash</button>
                 <button v-if="activeTab === 'trash'" @click="bulkRestore" :disabled="selectedThreads.length === 0" class="bulk-btn restore-btn">Restore</button>
                 <button v-if="activeTab === 'trash'" @click="bulkPermanentlyDelete" :disabled="selectedThreads.length === 0" class="bulk-btn delete-btn">Delete</button>
              </div>
           </div>
        </div>

        <!-- Chat Pane -->
        <div class="messenger-chat-pane" :class="{ 'hidden-on-mobile': rightPaneMode === 'none' }">
           
           <!-- State: NO CHAT SELECTED -->
           <div v-if="rightPaneMode === 'none'" class="no-chat-selected">
              <div class="no-chat-icon"><span class="material-symbols-outlined">forum</span></div>
              <h3>Your Messages</h3>
              <p>Select a conversation from the sidebar or start a new one.</p>
           </div>

           <!-- State: COMPOSE MESSAGE -->
           <div v-else-if="rightPaneMode === 'compose'" class="compose-pane">
              <div class="chat-header">
                 <div class="chat-header-info" style="display: flex; align-items: center; gap: 0.5rem;">
                    <button class="mobile-back-btn" @click="rightPaneMode = 'none'"><span class="material-symbols-outlined">arrow_back</span></button>
                    <h3>New Message</h3>
                 </div>
                 <div class="chat-header-actions">
                    <button @click="rightPaneMode = 'none'" class="icon-btn" title="Cancel"><span class="material-symbols-outlined">close</span></button>
                 </div>
              </div>
              
              <div class="compose-body">
                <div class="form-group">
                  <label class="form-label">Select Recipients:</label>
                  
                  <div class="filter-group">
                    <label class="sub-label">Filters (Optional):</label>
                    <div class="role-buttons">
                      <button v-for="role in roles" :key="role.value" @click="selectRole(role.value)" :class="['role-btn', { active: selectedRole === role.value }]">
                        {{ role.label }}
                      </button>
                    </div>
                    <div v-if="selectedRole && selectedRole !== 'Director' && selectedRole !== 'Staff' && !isTWG" class="office-buttons" style="margin-top: 0.5rem;">
                      <button v-for="office in offices" :key="office.value" @click="selectOffice(office.value)" :class="['office-btn', { active: selectedOffice === office.value }]">
                        {{ office.label }}
                      </button>
                    </div>
                  </div>

                  <div class="filter-group">
                    <label class="sub-label">Search Users by Name or Email:</label>
                    <input v-model="userSearchQuery" type="text" class="form-input" placeholder="Type to search..." style="margin-bottom: 0.5rem;">
                    
                    <div class="user-list">
                      <label v-for="user in filteredUsers" :key="user.id" class="user-item">
                        <input type="checkbox" :value="user.id" v-model="selectedUsers">
                        <span>{{ user.full_name }} <span class="user-email" v-if="user.email">({{ user.email }})</span></span>
                      </label>
                      <div v-if="filteredUsers.length === 0" class="no-users-msg">No users found.</div>
                    </div>
                  </div>
                  
                  <div v-if="selectedUsers.length > 0" class="filter-group" style="margin-top: 1rem;">
                    <label class="sub-label">Selected Recipients:</label>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                       <span v-for="userId in selectedUsers" :key="'sel_'+userId" style="background: rgba(147, 51, 234, 0.2); color: #e9d5ff; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.25rem; border: 1px solid rgba(147,51,234,0.4);">
                          {{ getUserName(userId) }}
                          <span @click="removeUser(userId)" class="material-symbols-outlined" style="font-size: 1rem; cursor: pointer;">close</span>
                       </span>
                    </div>
                  </div>
                </div>

                <div v-if="selectedUsers.length > 0" class="form-group">
                  <label class="form-label">Attach Documents (Optional):</label>
                  <div class="checkbox-list" style="max-height: 150px; overflow-y: auto; padding: 0.5rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; background: rgba(0,0,0,0.2); margin-top: 0.5rem;">
                    <label v-for="doc in allUserDocuments" :key="doc.id" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0; cursor: pointer; color: #f8fafc; font-size: 0.9rem;">
                      <input type="checkbox" :value="doc.id" v-model="selectedDocuments" style="accent-color: #9333ea;">
                      {{ doc.title }} <span style="color:#94a3b8; font-size: 0.8rem;">({{ doc.type }})</span>
                    </label>
                    <div v-if="allUserDocuments.length === 0" style="color: #94a3b8; font-size: 0.9rem; padding: 0.5rem; text-align: center;">No documents available.</div>
                  </div>
                </div>



                <div v-if="selectedUsers.length > 0" class="form-group">
                  <label class="form-label">Message:</label>
                  <textarea v-model="messageText" class="form-textarea" rows="4" placeholder="Enter your message"></textarea>
                </div>
                
                <div v-if="messageText" class="panel-footer" style="padding-bottom: 2rem;">
                  <button @click="sendMessage" class="btn-primary" style="width: 100%;">Send Message</button>
                </div>
              </div>
           </div>

           <!-- State: MAKE ANNOUNCEMENT -->
           <div v-else-if="rightPaneMode === 'announce'" class="compose-pane">
              <div class="chat-header">
                 <div class="chat-header-info" style="display: flex; align-items: center; gap: 0.5rem;">
                    <button class="mobile-back-btn" @click="rightPaneMode = 'none'"><span class="material-symbols-outlined">arrow_back</span></button>
                    <h3 style="color: #c084fc; display: flex; align-items: center; gap: 0.5rem;"><span class="material-symbols-outlined">campaign</span> Make Announcement</h3>
                 </div>
                 <div class="chat-header-actions">
                    <button @click="rightPaneMode = 'none'" class="icon-btn" title="Cancel"><span class="material-symbols-outlined">close</span></button>
                 </div>
              </div>
              
              <div class="compose-body">
                <div class="form-group">
                  <label class="form-label">Target Audience:</label>
                  <select v-model="announceTargetType" class="form-control">
                    <option value="">Select Audience...</option>
                    <option value="all">Whole University</option>
                    <option value="role">Specific Role</option>
                    <option value="office">Specific Office / Unit</option>
                  </select>
                </div>

                <div v-if="announceTargetType === 'role'" class="form-group">
                  <label class="form-label">Select Role:</label>
                  <select v-model="announceTargetValue" class="form-control">
                    <option value="">Select Role...</option>
                    <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                  </select>
                </div>

                <div v-if="announceTargetType === 'office'" class="form-group">
                  <label class="form-label">Select Office:</label>
                  <select v-model="announceTargetValue" class="form-control">
                    <option value="">Select Office...</option>
                    <option v-for="office in offices" :key="office.value" :value="office.value">{{ office.label }}</option>
                  </select>
                </div>



                <div v-if="announceTargetType && (announceTargetType === 'all' || announceTargetValue)" class="form-group">
                  <label class="form-label">Message:</label>
                  <textarea v-model="announceMessage" class="form-textarea" rows="6" placeholder="Enter your announcement details"></textarea>
                </div>

                <div v-if="announceMessage" class="panel-footer" style="padding-bottom: 2rem;">
                  <button @click="sendAnnouncement" class="btn-primary" style="width: 100%; background: linear-gradient(135deg, #9333ea, #c084fc);">Broadcast Announcement</button>
                </div>
              </div>
           </div>
           
           <!-- State: ACTIVE CHAT -->
           <div v-else-if="rightPaneMode === 'chat' && activeConversationId" class="active-chat-wrapper">
              <!-- Chat Header -->
              <div class="chat-header">
                 <div class="chat-header-info" style="display: flex; align-items: center; gap: 0.5rem;">
                    <button class="mobile-back-btn" @click="rightPaneMode = 'none'"><span class="material-symbols-outlined">arrow_back</span></button>
                    <div>
                        <h3>{{ activeConversation?.other_party }}</h3>
                        <span class="chat-role">{{ formatRole(activeConversation?.role) }} &bull; {{ getOfficeName(activeConversation?.office_id) }}</span>
                    </div>
                 </div>
                 <div class="chat-header-actions">
                    <button v-if="activeTab !== 'trash'" @click="trashConversation(activeConversation?.thread_id)" class="icon-btn danger-icon-btn" title="Move to Trash">
                       <span class="material-symbols-outlined">delete</span>
                    </button>
                    <button v-if="activeTab === 'trash'" @click="restoreConversation(activeConversation?.thread_id)" class="icon-btn success-icon-btn" title="Restore Chat">
                       <span class="material-symbols-outlined">restore_from_trash</span>
                    </button>
                    <button v-if="activeTab === 'trash'" @click="permanentlyDeleteConversation(activeConversation?.thread_id)" class="icon-btn danger-icon-btn" title="Permanently Delete">
                       <span class="material-symbols-outlined">delete_forever</span>
                    </button>
                 </div>
              </div>
              
              <!-- Chat History -->
              <div class="chat-history" ref="chatHistoryContainer">
                 <div v-if="loadingThread" class="loading-state"><div class="spinner"></div></div>
                 <div v-else class="chat-bubbles-container">
                    <div v-for="msg in activeThreadHistory" :key="msg.id" 
                         class="chat-message-row"
                         :class="{'sent': msg.sender_id == user.id, 'received': msg.sender_id != user.id}">
                       
                       <div class="chat-bubble">

                          
                          <div class="msg-text">{{ msg.message }}</div>
                          
                          <div v-if="msg.document_id" class="msg-attachments">
                             <div class="attachment-label"><span class="material-symbols-outlined">attachment</span> Attachments</div>
                             <div class="attachment-list">
                                <button v-for="docId in msg.document_id.split(',')" :key="docId" 
                                        @click="docId.startsWith('design_') ? viewAttachedDesign(docId) : viewAttachedReport(docId)" 
                                        class="attachment-btn">
                                   View {{ docId.startsWith('design_') ? 'Design' : 'Report' }}
                                </button>
                             </div>
                          </div>
                          
                          <div class="msg-time">{{ msg.date }}</div>
                       </div>
                    </div>
                 </div>
              </div>
              
              <!-- Chat Composer (Hidden if it's an announcement) -->
              <div class="chat-composer" v-if="activeTab !== 'trash' && !activeConversation?.is_announcement">
                 <div v-if="composerDocuments.length > 0" class="composer-attachments-preview">
                    <span v-for="docId in composerDocuments" :key="docId" class="doc-chip">
                       {{ getDocTitle(docId) }}
                       <span @click="composerDocuments = composerDocuments.filter(id => id !== docId)" class="material-symbols-outlined">close</span>
                    </span>
                 </div>
                 
                 <div class="composer-input-area">
                    <button @click="showAttachmentMenu = !showAttachmentMenu" class="icon-btn attach-btn" title="Attach Document">
                       <span class="material-symbols-outlined">attach_file</span>
                    </button>
                    
                    <textarea 
                       v-model="composerText" 
                       @keydown.enter.prevent="handleEnter"
                       placeholder="Type a message..." 
                       rows="1"
                       ref="composerTextarea"
                       @input="adjustTextareaHeight"
                       class="composer-textarea"></textarea>
                       
                    <button @click="sendReply" :disabled="!composerText.trim() && composerDocuments.length === 0" class="send-btn" title="Send">
                       <span class="material-symbols-outlined">send</span>
                    </button>
                 </div>
                 
                 <div v-if="showAttachmentMenu" class="attachment-menu">
                    <div class="attachment-menu-header">
                       <h4>Select Documents</h4>
                       <button @click="showAttachmentMenu = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="attachment-menu-list">
                       <label v-for="doc in activeChatUserDocs" :key="doc.id" class="doc-checkbox">
                          <input type="checkbox" :value="doc.id" v-model="composerDocuments">
                          <span>{{ doc.title }} ({{ doc.type }})</span>
                       </label>
                       <div v-if="activeChatUserDocs.length === 0" class="no-docs">No documents found.</div>
                    </div>
                 </div>
</div>
              <div class="announcement-banner" v-else-if="activeConversation?.is_announcement">
                 <span class="material-symbols-outlined">campaign</span>
                 <p>This is a broadcast announcement. Replies are disabled.</p>
              </div>
           </div>
        </div>
      </div>
    </div>
            </main>
</template>


<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';
import CommunicationsHeader from '../../components/CommunicationsHeader.vue';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

// Extracted Specifics
const roles = [
  { value: 'Director', label: 'Director' },
  { value: 'Staff', label: 'Staff' }
];
const formatRole = (role) => {
  if (!role) return '';
  if (role.toLowerCase() === 'non-twg') return 'Proponent';
  return role;
};
const isTWG = ref(true);
const hasAnnouncementFeature = ref(false);
const viewAttachedDesign = (docId) => { if (!docId) return; router.push({ name: 'college-ad-view', params: { id: docId.replace('design_', '') } }); };
const viewAttachedReport = (docId) => { if (!docId) return; router.push({ name: 'college-ar-view', params: { id: docId.replace('report_', '') } }); };

// Layout State
const activeTab = ref('chats');
const isSelectionMode = ref(false);
const selectedThreads = ref([]);



 
const loadingMessages = ref(true);
const conversations = ref([]);
const trashedConversations = ref([]);
const activeConversationId = ref(null);
const activeConversation = ref(null);
const activeThreadHistory = ref([]);
const loadingThread = ref(false);
const chatHistoryContainer = ref(null);

// Right Pane Mode: 'none', 'chat', 'compose', 'announce'
const rightPaneMode = ref('none');

// Compose State
const selectedRole = ref('');
const selectedOffice = ref('');
const userSearchQuery = ref('');
const selectedUsers = ref([]);
const selectedDocuments = ref([]);
const allUserDocuments = ref([]);
const messageTitle = ref('');
const messageText = ref('');
const allUsers = ref([]);
const offices = ref([]);

// Announce State
const announceTargetType = ref('');
const announceTargetValue = ref('');
const announceTitle = ref('');
const announceMessage = ref('');

// Inline Chat Composer State
const composerText = ref('');
const composerDocuments = ref([]);
const showAttachmentMenu = ref(false);
const activeChatUserDocs = ref([]);
const composerTextarea = ref(null);

const getUserName = (id) => {
  const u = allUsers.value.find(x => x.id === id);
  return u ? u.full_name : 'Unknown';
};

const getOfficeName = (id) => {
  if (!id) return 'Unknown Office';
  const office = offices.value.find(o => String(o.value) === String(id));
  return office ? office.label : 'Unknown Office';
};

const removeUser = (id) => {
  selectedUsers.value = selectedUsers.value.filter(u => u !== id);
};

const adjustTextareaHeight = () => {
  if (composerTextarea.value) {
    composerTextarea.value.style.height = 'auto';
    composerTextarea.value.style.height = Math.min(composerTextarea.value.scrollHeight, 120) + 'px';
  }
};

const handleEnter = (e) => {
  if (!e.shiftKey) {
    sendReply();
  } else {
    composerText.value += '\n';
    adjustTextareaHeight();
  }
};

const filteredUsers = computed(() => {
  let users = allUsers.value;
  
  if (selectedRole.value) {
    users = users.filter(u => u.user_role === selectedRole.value);
  }
  if (selectedOffice.value) {
    users = users.filter(u => String(u.office_id) === String(selectedOffice.value));
  }
  
  if (userSearchQuery.value.trim()) {
    const q = userSearchQuery.value.toLowerCase();
    users = users.filter(u => 
      (u.full_name && u.full_name.toLowerCase().includes(q)) || 
      (u.email && u.email.toLowerCase().includes(q))
    );
  }
  
  return users;
});

const selectRole = (roleValue) => {
  selectedRole.value = roleValue === selectedRole.value ? '' : roleValue;
  selectedOffice.value = ''; // Auto-deselect office when role changes
};

const selectOffice = (officeValue) => {
  selectedOffice.value = officeValue === selectedOffice.value ? '' : officeValue;
};



const fetchMyDocuments = async () => {
  if (!user.value.id) return;
  try {
    const role = user.value.user_role || 'TWG';
    let designsUrl = `activity-designs/${user.value.id}`;
    let reportsUrl = `activity-reports/${user.value.id}`;
    if (role === 'Director' || role === 'Admin') {
       designsUrl = 'activity-designs';
       reportsUrl = 'activity-reports';
    }
    const [designsRes, reportsRes, archivesRes] = await Promise.all([
      api.get(designsUrl),
      api.get(reportsUrl),
      api.get(`archives?user_id=${user.value.id}&role=${role}`)
    ]);
    const activeDesigns = (designsRes.data?.data || []).map(d => ({
      id: `design_${d.act_design_id}`, title: `${d.title} (${d.status})`, type: 'Activity Design'
    }));
    const activeReports = (reportsRes.data?.data || []).map(r => ({
      id: `report_${r.id}`, title: `${r.title} (${r.status})`, type: 'Accomplishment Report'
    }));
    const archivesList = archivesRes.data?.data || [];
    const archivedDesigns = archivesList.filter(a => a.type === 'design').map(d => ({
      id: `design_${d.original_id}`, title: `${d.title} (Archived)`, type: 'Activity Design'
    }));
    const archivedReports = archivesList.filter(a => a.type === 'report').map(r => ({
      id: `report_${r.original_id}`, title: `${r.title} (Archived)`, type: 'Accomplishment Report'
    }));
    allUserDocuments.value = [...activeDesigns, ...activeReports, ...archivedDesigns, ...archivedReports];
  } catch(e) { console.error('Error fetching my docs:', e); }
};

const fetchUsers = async () => {
    try {
      const usersResponse = await api.get('users');
      allUsers.value = usersResponse.data;
      const officesResponse = await api.get('office_units');
      offices.value = officesResponse.data.map(office => ({
        value: office.unit_id || office.office_id,
        label: office.unit_name || office.office_name
      }));
    } catch (err) {
      console.error('Error fetching users/offices:', err);
    }
};

const processMessagesToConversations = (inbox, sent) => {
  const threads = {};
  
  const processMsg = (msg, type) => {
    const threadId = msg.parent_id || msg.id;
    const isUnread = type === 'inbox' && msg.is_read == 0;
    
    let displayParty = msg.sender;
    if (msg.is_announcement == 1) {
        if (type === 'sent' || msg.direction === 'sent') {
            displayParty = 'Broadcast Announcements';
        } else {
            // Role-based Announcements thread
            const role = (msg.role && msg.role.toLowerCase() === 'non-twg') ? 'Proponent' : (msg.role || 'System');
            displayParty = `${role} Announcements`;
        }
    }

    if (!threads[threadId]) {
       threads[threadId] = {
          thread_id: threadId,
          latest_msg: msg,
          other_party: displayParty,
          other_party_id: msg.sender_id,
          role: (msg.is_announcement == 1 && (type === 'sent' || msg.direction === 'sent')) ? (user.value.user_role || user.value.role) : msg.role,
          office_id: (msg.is_announcement == 1 && (type === 'sent' || msg.direction === 'sent')) ? user.value.office_id : msg.office_id,
          unread_count: isUnread ? 1 : 0,
          is_announcement: msg.is_announcement == 1
       };
    } else {
       if (new Date(msg.created_at || msg.date) > new Date(threads[threadId].latest_msg.created_at || threads[threadId].latest_msg.date)) {
          threads[threadId].latest_msg = msg;
          threads[threadId].other_party = displayParty;
          threads[threadId].other_party_id = msg.sender_id;
          threads[threadId].role = (msg.is_announcement == 1 && type === 'sent') ? (user.value.user_role || user.value.role) : msg.role;
          threads[threadId].office_id = (msg.is_announcement == 1 && type === 'sent') ? user.value.office_id : msg.office_id;
          threads[threadId].is_announcement = msg.is_announcement == 1;
       }
       if (isUnread) threads[threadId].unread_count++;
    }
  };

  inbox.forEach(m => processMsg(m, 'inbox'));
  sent.forEach(m => processMsg(m, 'sent'));

  return Object.values(threads).map(t => {
    const d = new Date(t.latest_msg.date);
    const now = new Date();
    let timeShort = t.latest_msg.date;
    if (d.toDateString() === now.toDateString()) {
      timeShort = d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } else {
      timeShort = d.toLocaleDateString([], {month: 'short', day: 'numeric'});
    }
    
    return {
      ...t,
      latest_time_short: timeShort,
      latest_time: d,
      preview: t.latest_msg.preview || 'Attachment'
    };
  }).sort((a, b) => b.latest_time - a.latest_time);
};

const fetchMessages = async () => {
  if (user.value.id) {
    loadingMessages.value = true;
    try {
      const [inboxRes, sentRes, trashRes] = await Promise.all([
         api.get(`messages/inbox/${user.value.id}`),
         api.get(`messages/sent/${user.value.id}`),
         api.get(`messages/trashed/${user.value.id}`)
      ]);
      
      if (inboxRes.data.success && sentRes.data.success) {
         conversations.value = processMessagesToConversations(inboxRes.data.data, sentRes.data.data);
      }
      if (trashRes.data.success) {
         trashedConversations.value = processMessagesToConversations(trashRes.data.data, []);
      }
    } catch (err) {
      console.error('Error fetching messages:', err);
    } finally {
      loadingMessages.value = false;
    }
  }
};

const filteredConversations = computed(() => {
   return activeTab.value === 'trash' ? trashedConversations.value : conversations.value;
});

const scrollToBottom = () => {
   nextTick(() => {
      if (chatHistoryContainer.value) {
         chatHistoryContainer.value.scrollTop = chatHistoryContainer.value.scrollHeight;
      }
   });
};

const toggleSelectionMode = () => {
    isSelectionMode.value = !isSelectionMode.value;
    if (!isSelectionMode.value) {
        selectedThreads.value = [];
    }
};

const toggleThreadSelection = (threadId, event) => {
    event.stopPropagation();
    const index = selectedThreads.value.indexOf(threadId);
    if (index === -1) {
        selectedThreads.value.push(threadId);
    } else {
        selectedThreads.value.splice(index, 1);
    }
};

const bulkTrash = async () => {
    if (selectedThreads.value.length === 0) return;
    try {
        const res = await api.post('messages/bulk-trash', {
            user_id: user.value.id,
            message_ids: selectedThreads.value
        });
        if (res.data.success) {
            Swal.fire({ icon: 'success', title: 'Trashed', text: 'Selected conversations moved to trash.', timer: 1500, showConfirmButton: false });
            isSelectionMode.value = false;
            selectedThreads.value = [];
            fetchMessages();
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to trash conversations' });
    }
};

const bulkRestore = async () => {
    if (selectedThreads.value.length === 0) return;
    try {
        const res = await api.post('messages/bulk-restore', {
            user_id: user.value.id,
            message_ids: selectedThreads.value
        });
        if (res.data.success) {
            Swal.fire({ icon: 'success', title: 'Restored', text: 'Selected conversations restored.', timer: 1500, showConfirmButton: false });
            isSelectionMode.value = false;
            selectedThreads.value = [];
            fetchMessages();
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to restore conversations' });
    }
};

const bulkPermanentlyDelete = async () => {
    if (selectedThreads.value.length === 0) return;
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this! Selected conversations will be deleted permanently.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, delete permanently!'
    });
    if (result.isConfirmed) {
        try {
            const res = await api.post('messages/permanently-delete', {
                user_id: user.value.id,
                message_ids: selectedThreads.value
            });
            if (res.data.success) {
                Swal.fire({ icon: 'success', title: 'Deleted', text: 'Selected conversations deleted permanently.', timer: 1500, showConfirmButton: false });
                isSelectionMode.value = false;
                selectedThreads.value = [];
                fetchMessages();
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete permanently' });
        }
    }
};

const selectConversation = async (conv) => {
   if (isSelectionMode.value) {
       const index = selectedThreads.value.indexOf(conv.thread_id);
       if (index === -1) selectedThreads.value.push(conv.thread_id);
       else selectedThreads.value.splice(index, 1);
       return;
   }
   activeConversationId.value = conv.thread_id;
   activeConversation.value = conv;
   rightPaneMode.value = 'chat';
   conv.unread_count = 0;
   
   loadingThread.value = true;
   activeThreadHistory.value = [];
   composerText.value = '';
   composerDocuments.value = [];
   showAttachmentMenu.value = false;
   
   try {
      const res = await api.get(`messages/thread/${conv.thread_id}`);
      if (res.data.success) {
         activeThreadHistory.value = res.data.data;
         
         activeThreadHistory.value.forEach(msg => {
            if (String(msg.sender_id) !== String(user.value.id) && msg.is_read == 0) {
               api.post(`messages/read/${msg.id}`, {}).catch(()=>{}).then(()=>{
                  conv.unread_count = 0; 
               });
            }
         });
         
         scrollToBottom();
         
         activeChatUserDocs.value = allUserDocuments.value;
      }
   } catch (err) {
      console.error('Error fetching thread:', err);
   } finally {
      loadingThread.value = false;
   }
};

const getDocTitle = (docId) => {
   const d = activeChatUserDocs.value.find(x => x.id === docId);
   return d ? d.title : docId;
};

const sendMessage = async () => {
  if (!user.value.id) return;
  try {
    let threadId = null;
    if (selectedUsers.value.length === 1) {
        const targetId = selectedUsers.value[0];
        const existingConv = conversations.value.find(c => String(c.other_party_id) === String(targetId) && !c.is_announcement);
        if (existingConv) {
            threadId = existingConv.thread_id;
        }
    }

    const payload = {
      sender_id: user.value.id,
      to: selectedUsers.value,
      parent_id: threadId,
      title: messageTitle.value,
      message: messageText.value,
      document_type: selectedDocuments.value.length > 0 ? 'Mixed' : null,
      document_id: selectedDocuments.value.length > 0 ? selectedDocuments.value.join(',') : null
    };
    
    Swal.fire({
      title: 'Sending...',
      text: 'Please wait while your message is sent and email notifications are dispatched.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const response = await api.post('messages/send', payload);
    if (response.data.success) {
      if (threadId) {
         const existingConv = conversations.value.find(c => c.thread_id === threadId);
         if (existingConv) {
            selectConversation(existingConv);
         } else {
            rightPaneMode.value = 'none';
         }
      } else {
         rightPaneMode.value = 'none';
      }
      
      selectRole('');
      selectedOffice.value = '';
      userSearchQuery.value = '';
      selectedUsers.value = [];
      messageTitle.value = '';
      messageText.value = '';
      selectedDocuments.value = [];
      Swal.fire({ icon: 'success', title: 'Success!', text: 'Message sent successfully.', timer: 1500, showConfirmButton: false });
      fetchMessages();
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to send message.' });
  }
};

const sendAnnouncement = async () => {
  if (!user.value.id) return;
  if (!announceTargetType.value) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Please select a target audience.' });
    return;
  }
  if (announceTargetType.value !== 'all' && !announceTargetValue.value) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Please select a specific role or office.' });
    return;
  }
  if (!announceMessage.value) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Please fill in all fields.' });
    return;
  }

  try {
    const payload = {
      sender_id: user.value.id,
      target_type: announceTargetType.value,
      target_value: announceTargetValue.value,
      title: announceTitle.value,
      message: announceMessage.value
    };
    
    Swal.fire({
      title: 'Sending Announcement...',
      text: 'Please wait while your announcement is broadcasted.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const response = await api.post('messages/announce', payload);
    if (response.data.success) {
      rightPaneMode.value = 'none';
      announceTargetType.value = '';
      announceTargetValue.value = '';
      announceTitle.value = '';
      announceMessage.value = '';
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Announcement broadcasted successfully.',
        confirmButtonColor: '#3b82f6'
      });
      fetchMessages();
    }
  } catch (err) {
    console.error('Error sending announcement:', err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to send announcement.' });
  }
};

const sendReply = async () => {
  if (!composerText.value.trim() && composerDocuments.value.length === 0) return;
  if (!activeConversation.value) return;

  const originalTitle = activeThreadHistory.value[0]?.title || '';
  const replyTitle = originalTitle.startsWith('Re:') ? originalTitle : (originalTitle ? `Re: ${originalTitle}` : 'Re: Message');

  try {
    const payload = {
      sender_id: user.value.id,
      to: [activeConversation.value.other_party_id],
      parent_id: activeConversation.value.thread_id,
      title: replyTitle,
      message: composerText.value.trim(),
      document_type: composerDocuments.value.length > 0 ? 'Mixed' : null,
      document_id: composerDocuments.value.length > 0 ? composerDocuments.value.join(',') : null
    };
    
    // Notice: We don't block the UI with Swal for simple chat replies if we want a seamless chat experience.
    // However, since email dispatch takes 2-5 seconds, a small loader is necessary to prevent double-sends.
    Swal.fire({
      title: 'Sending...',
      text: 'Please wait while your reply is sent and email notifications are dispatched.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const response = await api.post('messages/send', payload);
    Swal.close();
    if (response.data.success) {
      composerText.value = '';
      composerDocuments.value = [];
      showAttachmentMenu.value = false;
      adjustTextareaHeight();
      
      selectConversation(activeConversation.value);
      fetchMessages(); 
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to send reply.' });
  }
};

const trashConversation = async (threadId) => {
   if(!threadId) return;
   try {
     const res = await api.post(`messages/trash/${threadId}`, { user_id: user.value.id });
     if (res.data.success) {
       Swal.fire({ icon: 'success', title: 'Trashed', text: 'Conversation moved to trash.', timer: 1500, showConfirmButton: false });
       rightPaneMode.value = 'none';
       activeConversationId.value = null;
       activeConversation.value = null;
       fetchMessages();
     }
   } catch (err) {
     Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to trash conversation' });
   }
};

const restoreConversation = async (threadId) => {
   if(!threadId) return;
   try {
     const res = await api.post(`messages/restore/${threadId}`, { user_id: user.value.id });
     if (res.data.success) {
       Swal.fire({ icon: 'success', title: 'Restored', text: 'Conversation restored.', timer: 1500, showConfirmButton: false });
       rightPaneMode.value = 'none';
       activeConversationId.value = null;
       activeConversation.value = null;
       fetchMessages();
     }
   } catch (err) {
     Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to restore conversation' });
   }
};

const permanentlyDeleteConversation = async (threadId) => {
   if(!threadId) return;
   const result = await Swal.fire({
     title: 'Are you sure?',
     text: "You won't be able to revert this!",
     icon: 'warning',
     showCancelButton: true,
     confirmButtonColor: '#ef4444',
     cancelButtonColor: '#64748b',
     confirmButtonText: 'Yes, delete permanently!'
   });
   if (result.isConfirmed) {
     try {
       const res = await api.post('messages/permanently-delete', { 
         user_id: user.value.id, 
         message_ids: [threadId] 
       });
       if (res.data.success) {
         Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Conversation permanently deleted.', timer: 1500, showConfirmButton: false });
         rightPaneMode.value = 'none';
         activeConversationId.value = null;
         activeConversation.value = null;
         fetchMessages();
       }
     } catch (err) {
       Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete permanently' });
     }
   }
};

watch(activeTab, () => {
   rightPaneMode.value = 'none';
   activeConversationId.value = null;
   activeConversation.value = null;
   isSelectionMode.value = false;
   selectedThreads.value = [];
});

onMounted(() => {
  fetchUsers();
  fetchMyDocuments();
  fetchMessages();
});
</script>


<style scoped>
.messages-main-content { padding: 0; flex-grow: 1; height: 100%; display: flex; flex-direction: column; }
.messages-content-wrapper { display: flex; flex-direction: column; flex-grow: 1; height: calc(100vh - 2rem); min-height: 95vh; }
.header-section { flex-shrink: 0; }
.page-title { font-size: 2rem; color: #1e293b; font-weight: 900; letter-spacing: -0.025em; margin-bottom: 0.25rem; }
.page-subtitle { color: #64748b; font-size: 0.95rem; }

/* System Dark Theme inside the app container */
.messenger-container {
   flex-grow: 1;
   display: flex;
   background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
   border: 1px solid rgba(185, 121, 204, 0.15);
   border-radius: 1rem;
   overflow: hidden;
   box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);
   backdrop-filter: blur(8px);
   max-width: 100%;
}

.messenger-sidebar {
   width: 350px;
   background: rgba(0, 0, 0, 0.2);
   border-right: 1px solid rgba(185, 121, 204, 0.15);
   display: flex;
   flex-direction: column;
   flex-shrink: 0;
}

.sidebar-top {
   padding: 1rem;
   border-bottom: 1px solid rgba(185, 121, 204, 0.15);
}

.sidebar-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 1rem;
}
.sidebar-header h2 { color: #f8fafc; font-size: 1.25rem; margin: 0; font-weight: 700; }
.sidebar-actions { display: flex; gap: 0.5rem; }
.icon-btn { 
   background: transparent; border: none; color: #94a3b8; cursor: pointer; 
   width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
   transition: background 0.2s, color 0.2s;
}
.icon-btn:hover { background: rgba(185, 121, 204, 0.1); color: #f8fafc; }
.primary-icon-btn { background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); color: white; }
.primary-icon-btn:hover { box-shadow: 0 4px 12px rgba(147, 51, 234, 0.4); transform: translateY(-1px); }
.danger-icon-btn:hover { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
.success-icon-btn:hover { background: rgba(34, 197, 94, 0.2); color: #86efac; }

.sidebar-tabs {
   display: flex;
   background: rgba(0,0,0,0.3);
   border-radius: 0.5rem;
   padding: 0.25rem;
   border: 1px solid rgba(185, 121, 204, 0.1);
}
.sidebar-tabs button {
   flex: 1; padding: 0.5rem; border: none; background: transparent; color: #94a3b8;
   border-radius: 0.35rem; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s;
}
.sidebar-tabs button.active {
   background: rgba(147, 51, 234, 0.2); color: #c084fc; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid rgba(147, 51, 234, 0.3);
}

.conversation-list {
   flex-grow: 1;
   overflow-y: auto;
   padding: 0.5rem;
}
.conversation-item {
   display: flex; gap: 0.75rem; padding: 0.75rem; border-radius: 0.5rem;
   cursor: pointer; transition: background 0.2s; margin-bottom: 0.25rem; border: 1px solid transparent;
}
.conversation-item:hover { background: rgba(185, 121, 204, 0.05); border-color: rgba(185, 121, 204, 0.1); }
.conversation-item.active { background: rgba(185, 121, 204, 0.15); border-color: rgba(185, 121, 204, 0.3); }

.conv-avatar {
   width: 44px; height: 44px; border-radius: 50%; background: rgba(0,0,0,0.3); border: 1px solid rgba(185, 121, 204, 0.2);
   display: flex; align-items: center; justify-content: center; color: #c084fc; position: relative; flex-shrink: 0;
}
.unread-badge {
   position: absolute; top: -2px; right: -2px; width: 12px; height: 12px;
   background: #3b82f6; border-radius: 50%; border: 2px solid #16213e;
}

.conv-details { flex-grow: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
.conv-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem; }
.conv-name { color: #f8fafc; font-weight: 600; font-size: 1.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-name.unread { color: #60a5fa; font-weight: 700; }
.conv-time { color: #64748b; font-size: 0.75rem; white-space: nowrap; }
.conv-role { color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-preview { color: #94a3b8; font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-preview.unread { color: #cbd5e1; font-weight: 500; }

.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #64748b; text-align: center; padding: 2rem; }
.empty-state .material-symbols-outlined { font-size: 3rem; color: rgba(148, 163, 184, 0.2); margin-bottom: 1rem; }

.messenger-chat-pane {
   flex-grow: 1;
   display: flex;
   flex-direction: column;
   background: transparent;
   position: relative;
   min-width: 0;
}

.no-chat-selected {
   display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;
   color: #64748b; text-align: center;
}
.no-chat-icon { width: 80px; height: 80px; border-radius: 50%; background: rgba(0,0,0,0.2); border: 1px solid rgba(185, 121, 204, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; }
.no-chat-icon span { font-size: 2.5rem; color: rgba(147, 51, 234, 0.4); }

.active-chat-wrapper, .compose-pane { display: flex; flex-direction: column; height: 100%; }

.chat-header {
   padding: 1rem 1.5rem; border-bottom: 1px solid rgba(185, 121, 204, 0.15);
   display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2);
}
.chat-header-info h3 { color: #f8fafc; margin: 0 0 0.25rem 0; font-size: 1.1rem; }
.chat-role { color: #94a3b8; font-size: 0.85rem; }
.chat-header-actions { display: flex; gap: 0.5rem; }

.mobile-back-btn {
   display: none; background: transparent; border: none; color: #f8fafc; padding: 0.25rem;
   margin-right: 0.5rem; cursor: pointer; border-radius: 50%; align-items: center; justify-content: center;
}
.mobile-back-btn:hover { background: rgba(255,255,255,0.1); }

@media (max-width: 768px) {
   .mobile-back-btn { display: flex; }
   .hidden-on-mobile { display: none !important; }
   .messenger-sidebar { width: 100%; border-right: none; }
}

.chat-history {
   flex-grow: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem;
}
.chat-bubbles-container { display: flex; flex-direction: column; gap: 1rem; }
.chat-message-row { display: flex; width: 100%; }
.chat-message-row.sent { justify-content: flex-end; }
.chat-message-row.received { justify-content: flex-start; }

.chat-bubble {
   max-width: 85%; padding: 0.75rem 1rem; border-radius: 1rem; position: relative;
   display: flex; flex-direction: column; gap: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.chat-message-row.received .chat-bubble {
   background: rgba(0,0,0,0.3); color: #f8fafc; border-bottom-left-radius: 0.25rem; border: 1px solid rgba(185, 121, 204, 0.1);
}
.chat-message-row.sent .chat-bubble {
   background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); color: white; border-bottom-right-radius: 0.25rem;
}

.msg-title { font-weight: 700; font-size: 1.3rem; margin: 0; padding-bottom: 0.25rem; border-bottom: 1px solid rgba(255,255,255,0.2); }
.msg-text { font-size: 1.25rem; line-height: 1.6; white-space: pre-wrap; }
.msg-time { font-size: 0.7rem; opacity: 0.7; align-self: flex-end; margin-top: 0.25rem; font-family: monospace; }

.msg-attachments {
   background: rgba(0,0,0,0.15); padding: 0.5rem; border-radius: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; border: 1px solid rgba(255,255,255,0.1);
}
.attachment-label { font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 0.25rem; opacity: 0.9; }
.attachment-label span { font-size: 1rem; }
.attachment-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.attachment-btn {
   background: rgba(255,255,255,0.2); border: none; color: white; padding: 0.25rem 0.5rem;
   border-radius: 0.25rem; font-size: 0.8rem; cursor: pointer; transition: background 0.2s;
}
.attachment-btn:hover { background: rgba(255,255,255,0.3); }

.chat-composer {
   padding: 1rem 1.5rem; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(185, 121, 204, 0.15);
   position: relative;
}
.composer-input-area {
   display: flex; align-items: flex-end; gap: 0.75rem; background: rgba(0,0,0,0.3);
   border: 1px solid rgba(185, 121, 204, 0.2); padding: 0.5rem; border-radius: 1.5rem; transition: border-color 0.3s;
}
.composer-input-area:focus-within { border-color: #c084fc; box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.2); }

.composer-textarea {
   flex-grow: 1; background: transparent; border: none; color: #f8fafc; font-size: 1.15rem;
   font-family: inherit; resize: none; padding: 0.5rem 0; outline: none; max-height: 120px; line-height: 1.5;
}
.composer-textarea::placeholder { color: #64748b; }

.attach-btn { color: #94a3b8; }
.attach-btn:hover { color: #c084fc; background: rgba(147,51,234,0.1); }
.send-btn {
   background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); border: none; color: white; width: 36px; height: 36px; border-radius: 50%;
   display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
}
.send-btn:hover:not(:disabled) { transform: scale(1.05); box-shadow: 0 4px 10px rgba(147, 51, 234, 0.4); }
.send-btn:disabled { background: rgba(0,0,0,0.3); color: #64748b; cursor: not-allowed; border: 1px solid rgba(255,255,255,0.1); }

.attachment-menu {
   position: absolute; bottom: 100%; left: 1.5rem; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: 1px solid rgba(185, 121, 204, 0.3);
   border-radius: 0.75rem; padding: 1rem; width: 300px; box-shadow: 0 -5px 15px rgba(0,0,0,0.5); margin-bottom: 0.5rem; z-index: 10;
}
.attachment-menu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
.attachment-menu-header h4 { margin: 0; color: #f8fafc; font-size: 0.95rem; }
.attachment-menu-header button { background: none; border: none; color: #94a3b8; cursor: pointer; }
.attachment-menu-list { display: flex; flex-direction: column; gap: 0.5rem; max-height: 200px; overflow-y: auto; }
.doc-checkbox { display: flex; align-items: center; gap: 0.5rem; color: #cbd5e1; font-size: 0.85rem; cursor: pointer; }
.doc-checkbox input { accent-color: #9333ea; }

.composer-attachments-preview { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem; }
.doc-chip {
   background: rgba(147, 51, 234, 0.2); color: #e9d5ff; padding: 0.25rem 0.5rem; border-radius: 0.25rem;
   font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem; border: 1px solid rgba(147,51,234,0.3);
}
.doc-chip span { font-size: 1rem; cursor: pointer; }

/* COMPOSE & ANNOUNCE BODY */
.compose-body { flex-grow: 1; padding: 1.5rem; overflow-y: auto; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; color: #f8fafc; margin-bottom: 0.5rem; font-size: 0.95rem; }
.sub-label { display: block; color: #cbd5e1; font-size: 0.85rem; margin-bottom: 0.25rem; }
.filter-group { margin-bottom: 1rem; }
.role-buttons, .office-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.role-btn, .office-btn { background: rgba(0,0,0,0.2); border: 1px solid rgba(185, 121, 204, 0.2); color: #cbd5e1; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.85rem; }
.role-btn.active, .office-btn.active { background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); color: white; border-color: transparent; }

.user-list { border: 1px solid rgba(185, 121, 204, 0.15); border-radius: 0.5rem; max-height: 200px; overflow-y: auto; padding: 0.5rem; background: rgba(0,0,0,0.2); }
.user-item { display: flex; align-items: center; gap: 0.5rem; color: #f8fafc; padding: 0.4rem 0.25rem; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); }
.user-item:last-child { border-bottom: none; }
.user-item input { accent-color: #9333ea; width: 16px; height: 16px; }
.user-email { color: #94a3b8; font-size: 0.8rem; }
.no-users-msg { color: #94a3b8; font-size: 0.9rem; padding: 0.5rem; text-align: center; }

.form-input, .form-textarea, select.form-control { width: 100%; padding: 0.75rem; border-radius: 0.5rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(185, 121, 204, 0.2); color: white; font-family: inherit; }
select.form-control option { background: #1e293b; color: #f8fafc; }
.form-textarea { resize: vertical; font-size: 1.15rem; line-height: 1.5; }

.panel-footer { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(185, 121, 204, 0.15); }
.btn-primary { background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); color: white; border: none; padding: 0.75rem 1.25rem; border-radius: 0.5rem; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(147, 51, 234, 0.2); transition: transform 0.2s; font-size: 1rem; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 12px rgba(147, 51, 234, 0.3); }

.spinner { border: 3px solid rgba(255,255,255,0.1); border-top-color: #9333ea; border-radius: 50%; width: 24px; height: 24px; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.loading-state { display: flex; justify-content: center; padding: 2rem; }

.announcement-banner {
   display: flex;
   align-items: center;
   justify-content: center;
   gap: 0.5rem;
   padding: 1rem;
   background: rgba(185, 121, 204, 0.1);
   color: #cbd5e1;
   border-top: 1px solid rgba(255, 255, 255, 0.05);
   font-style: italic;
   font-size: 0.95rem;
}
.announcement-banner .material-symbols-outlined {
   font-size: 1.25rem;
   color: #b979cc;
}


.conv-checkbox { display: flex; align-items: center; margin-right: 0.5rem; }
.conv-checkbox input { width: 18px; height: 18px; accent-color: #9333ea; cursor: pointer; }
.conversation-item.selected { background: rgba(147, 51, 234, 0.15); border-color: rgba(147, 51, 234, 0.3); }
.active-selection { background: rgba(147, 51, 234, 0.2) !important; color: #c084fc !important; }
.bulk-action-toolbar { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(0, 0, 0, 0.4); border-top: 1px solid rgba(185, 121, 204, 0.15); }
.selected-count { color: #f8fafc; font-size: 0.9rem; font-weight: 600; }
.bulk-actions { display: flex; gap: 0.5rem; }
.bulk-btn { border: none; padding: 0.4rem 0.75rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: white; transition: opacity 0.2s; }
.bulk-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.bulk-btn.trash-btn { background: #eab308; }
.bulk-btn.restore-btn { background: #3b82f6; }
.bulk-btn.delete-btn { background: #ef4444; }
</style>

