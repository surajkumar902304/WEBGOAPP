<template>
  <div>
    <v-row>
            <h2 class="text-h6 mb-1">Customers</h2>
        </v-row>
        <v-row class="mt-0 pt-0">
      <v-col cols="12">
        <v-text-field
          v-model="ssearch"
          clearable
          dense
          hide-details
          outlined
          prepend-inner-icon="mdi-magnify mb-2"
          placeholder="Search name"
        />
      </v-col>
    </v-row>

    <v-row> 
      <v-col cols="12" class="pt-0">
        <v-card outlined>
          <v-tabs v-model="activeTab" active-class="grey lighten-3" height="30">
            <v-tab class="text-none" style="font-size: 12px;">Approved</v-tab>
            <v-tab class="text-none" style="font-size: 12px;">Pending</v-tab>
            <v-tab class="text-none" style="font-size: 12px;">Declined</v-tab>
          </v-tabs>
            <v-data-table :items="filteredUsers" :headers="userHeaders" :search="ssearch" :footer-props="{
                      'items-per-page-options': [10, 25, 50, 100], 'items-per-page-text': 'Rows per page:'}">
        
              <template v-slot:item.admin_approval="{ item }">
                  <v-chip :color="item.admin_approval === 'Approved' ? 'green' : item.admin_approval === 'Declined' ? 'red darken-1' : 'orange'" class="ma-1" outlined pill small>
                      {{
                      item.admin_approval === 'Approved'
                          ? 'Approved'
                          : item.admin_approval === 'Declined'
                          ? 'Declined'
                          : 'Pending'
                      }}
                  </v-chip>
              </template>
              <template v-slot:item.user_details="{ item }">
                  <v-chip :color="'blue'" class="ma-1" outlined pill small @click="openUserDialog(item)">
                      View
                  </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                  <v-icon v-if="item.admin_approval === 'Pending'" color="green" class="mr-2" @click="changeStatus(item, 'Approved')" >mdi-check</v-icon>
                  <v-icon v-if="item.admin_approval === 'Pending' || item.admin_approval === 'Approved'" color="red" class="mr-2" @click="changeStatus(item, 'Declined')" >mdi-close</v-icon>
                  <v-icon v-if="item.admin_approval === 'Declined'" color="green" @click="changeStatus(item, 'Approved')" >mdi-check</v-icon>
              </template>
            </v-data-table>
        </v-card>
      </v-col>
    </v-row>

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
      userHeaders: [
        { text: 'ID', value: 'id' },
        { text: 'Name', value: 'name' },
        { text: 'Email', value: 'email' },
        { text: 'Status', value: 'admin_approval', sortable: false },
        { text: 'User Details', value: 'user_details', sortable: false },
        { text: 'Action', value: 'actions', sortable: false },
      ],
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
        user_id: user.id,
        admin_approval: newStatus,
        })
        .then(() => {
        this.$toast?.success(`Status updated to ${newStatus}.`, {
                        timeout: 500,
  hideProgressBar: true,
  icon: false
                    })
        })
        .catch(() => {
        this.$toast?.error("Failed to update status.", {
                        timeout: 500
                    })
        });
    }
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
.Vue-Toastification__toast {
  border-radius: 5px;
  padding: 10px 17px;
  font-size: 10px !important;
  min-width: 260px;
  min-height: 40px;
}

</style>
