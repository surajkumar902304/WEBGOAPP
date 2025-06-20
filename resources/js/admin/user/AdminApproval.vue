<template>
  <div>
    <v-row>
        <h2 class="text-h6 mb-1">Customers</h2>
    </v-row>
    <v-row class="mt-0 pt-0">
      <v-col cols="12">
        <v-text-field v-model="ssearch" clearable dense hide-details outlined prepend-inner-icon="mdi-magnify mb-2" placeholder="Search name"/>
      </v-col>
    </v-row>

    <v-row> 
      <v-col cols="12" class="pt-0">
        <v-card outlined>
          <v-row class="align-center" >
            <v-col>
              <v-tabs v-model="activeTab" active-class="grey lighten-3" height="30">
                <v-tab class="text-none" style="font-size: 12px;">Approved</v-tab>
                <v-tab class="text-none" style="font-size: 12px;">Pending</v-tab>
                <v-tab class="text-none" style="font-size: 12px;">Declined</v-tab>
              </v-tabs>
            </v-col>

            <v-col class="d-flex justify-end" cols="auto" v-if="selected.length > 0">
                <v-menu offset-y>
                    <template v-slot:activator="{ on, attrs }">
                        <span class="mr-2 font-weight-medium text-caption">{{ selected.length }} selected</span>
                    <v-icon color="primary" v-bind="attrs" v-on="on" style="cursor: pointer; margin-right: 5px;">
                        mdi-dots-vertical
                    </v-icon>
                    </template>
                    <v-list dense>
                        <v-list-item v-if="activeTab === 0" @click="openConfirmDialog('markDeclined')">
                            <v-list-item-title>Mark as Declined</v-list-item-title>
                        </v-list-item>
                        <v-list-item v-if="activeTab === 1" @click="openConfirmDialog('markApproved')">
                            <v-list-item-title>Mark as Approved</v-list-item-title>
                        </v-list-item>
                        <v-list-item v-if="activeTab === 1" @click="openConfirmDialog('markDeclined')">
                            <v-list-item-title>Mark as Declined</v-list-item-title>
                        </v-list-item>
                        <v-list-item v-if="activeTab === 2" @click="openConfirmDialog('markApproved')">
                            <v-list-item-title>Mark as Approved</v-list-item-title>
                        </v-list-item>
                      </v-list>
                </v-menu>
              </v-col> 
          </v-row>
          
          <v-data-table dense v-model="selected" :show-select="true" item-key="id" :items="filteredUsers" :headers="userHeaders" :search="ssearch" 
            :footer-props="{'items-per-page-options': [10, 25, 50, 100], 'items-per-page-text': 'Rows per page:'}">
            <template #header.admin_approval>
                <div class="text-center">Status</div>
            </template>
            <template v-slot:item.admin_approval="{ item }">
              <div class="text-center">
                <v-chip :color="item.admin_approval === 'Approved' ? 'green' : item.admin_approval === 'Declined' ? 'red darken-1' : 'orange'" outlined pill small>
                    {{ item.admin_approval === 'Approved' ? 'Approved' : item.admin_approval === 'Declined' ? 'Declined' : 'Pending' }}
                </v-chip>
              </div>
            </template>
            <template v-slot:item.index="{ index }">
              {{ index + 1 }}
            </template>
            <template #header.user_details>
                <div class="text-center">User Details</div>
            </template>
            <template v-slot:item.user_details="{ item }">
              <div class="text-center">
                <v-chip :color="'blue'" outlined pill small @click="openUserDialog(item)">View</v-chip>
              </div>
            </template>
            <template #header.action1>
                <div class="text-center">Action</div>
            </template>
            <template v-slot:item.action1="{ item }">
              <div class="text-center">
                <v-chip v-if="item.admin_approval === 'Pending' || item.admin_approval === 'Declined'" color="green" class="ma-1" outlined pill small 
                  style="cursor: pointer;" @click="changeStatus(item, 'Approved')">Approve</v-chip>
              </div>
            </template>
            <template #header.action2>
                <div class="text-center">Action</div>
            </template>
            <template v-slot:item.action2="{ item }">
              <div class="text-center">
                <v-chip v-if="item.admin_approval === 'Pending' || item.admin_approval === 'Approved'" color="red" class="ma-1" outlined pill small 
                  style="cursor: pointer;" @click="changeStatus(item, 'Declined')">Decline</v-chip>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="confirmDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h6">Confirm {{ actionLabel }}</v-card-title>
        <v-card-text>Are you sure you want to <strong>{{ actionLabel.toLowerCase() }}</strong> <strong>{{ selected.length }}</strong> selected users?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="confirmDialog = false">Cancel</v-btn>
          <v-btn text color="red" @click="executeBulkAction">Yes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- user Details dailog -->
    <v-dialog v-model="userDialog" max-width="500">
      <v-card>
        <v-card-title>
          <span class="text-h6">User Details</span>
          <v-spacer></v-spacer>
          <v-icon @click="userDialog = false" class="cursor-pointer">mdi-close</v-icon>
        </v-card-title>
        <v-card-text>
          <v-list dense v-if="selectedUser">
              <v-list-item><v-list-item-content><strong>Name:</strong> {{ selectedUser.name }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Email:</strong> {{ selectedUser.email }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Phone:</strong> {{ selectedUser.mobile }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Company Name:</strong> {{ selectedUser.company_name }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Address:</strong> {{ selectedUser.address1 }} {{ selectedUser.address2 }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>City:</strong> {{ selectedUser.city }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Country:</strong> {{ selectedUser.country }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Postcode:</strong> {{ selectedUser.postcode }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Rep Code:</strong> {{ selectedUser.rep_code ?? '' }}</v-list-item-content></v-list-item>
              <v-list-item><v-list-item-content><strong>Status:</strong> {{ selectedUser.admin_approval }}</v-list-item-content></v-list-item>
          </v-list>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: "AdminApproval",
  data() {
    return {
      users: [],
      activeTab: 0,
      userDialog: false,
      selectedUser: null,
      ssearch: '',
      
      selected: [],
      actionToConfirm: '',
      confirmDialog: false,
      actionLabel: '',
    };
  },
  computed: {
    filteredUsers() {
        return this.users.filter(user => {
        switch (this.activeTab) {
            case 0: return user.admin_approval === 'Approved';
            case 1: return user.admin_approval === 'Pending';
            case 2: return user.admin_approval === 'Declined';
            default: return true;
        }
        });
    },
    userHeaders() {
      const headers = [
        { text: 'Index', value: 'index', sortable: true, width: '100px' },
        { text: 'Name', value: 'name' },
        { text: 'Email', value: 'email' },
        { text: 'Status', value: 'admin_approval', sortable: false, width: '175px' },
        { text: 'User Details', value: 'user_details', sortable: false, width: '175px' },
      ];

      if (this.activeTab === 0) {
        headers.push({
          text: 'Action',
          value: 'action2',
          sortable: false,
          width: '175px',
        });
      } else {
        headers.push({
          text: 'Action',
          value: 'action1',
          sortable: false,
          width: '175px',
        });
      }
      if (this.activeTab === 1) {
        headers.push({
          text: 'Action',
          value: 'action2',
          sortable: false,
          width: '175px',
        });
      }

        return headers;
    }
  },
  watch: {
    activeTab() {
      this.selected = [];
    }
  },
  created() {
    this.getusers();
  },
  methods: {
    getusers() {
      axios.get('/admin/users/vlist')
        .then((resp) => {
          this.users = resp.data.users;
        });
    },
    openUserDialog(user) {
        this.selectedUser = user;
        this.userDialog = true;
    },
    changeStatus(user, newStatus) {
      user.admin_approval = newStatus;
      axios.post("/admin/users/update-approval", {
        user_ids: [user.id],
        bulkstatus: newStatus
      })
      .then(() => {
        this.$toast?.success(`Status updated to ${newStatus}.`, {
          timeout: 500,
          hideProgressBar: true,
          icon: false
        });
        this.getusers();
      })
      .catch(() => {
        this.$toast?.error("Failed to update status.", {
          timeout: 500
        });
      });
    },
    openConfirmDialog(action) {
      this.actionToConfirm = action;
      this.actionLabel = {
      markApproved: 'Mark as Approved',
      markDraft: 'Mark as Declined'
      }[action] || '';
      this.confirmDialog = true;
    },
    async executeBulkAction() {
      const ids = this.selected.map(p => p.id);
      let url = '';
      let payload = {};

      switch (this.actionToConfirm) {
      case 'markApproved':
      case 'markDeclined':
          url = '/admin/users/update-approval';
          payload = {
          user_ids: ids,
          bulkstatus: this.actionToConfirm === 'markApproved' ? 'Approved' : 'Declined'
          };
          break;
      }

      try {
      await axios.post(url, payload);
      this.$toast?.success(`${this.actionLabel} successful`, {
                  timeout: 500
              })
      this.getusers();
      } catch (err) {
      this.$toast?.error(`Failed to ${this.actionLabel.toLowerCase()}`, {
                  timeout: 500
              })
      } finally {
      this.confirmDialog = false;
      this.selected = [];
      }
    },
  }
}
</script>

<style>
.v-input__slot {
    min-height: 30px !important;
}
.v-input {
  font-size: 12px !important;
}
td.text-start {
    font-size: 13px !important;
}
.Vue-Toastification__toast {
  border-radius: 5px;
  padding: 10px 17px;
  font-size: 10px !important;
  min-width: 260px;
  min-height: 40px;
}
</style>
