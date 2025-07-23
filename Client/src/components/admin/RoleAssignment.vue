<template>
  <div class="role-assignment-container">
    <h2>Attribution des rôles</h2>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Email</th>
          <th>Rôle actuel</th>
          <th>Nouveau rôle</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role }}</td>
          <td>
            <select v-model="user.newRole" aria-label="Nouveau rôle pour {{ user.name }}">
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
          </td>
          <td>
            <button class="btn-primary" @click="$emit('assign-role', user)" aria-label="Attribuer le rôle à {{ user.name }}">Attribuer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "RoleAssignment",
  props: {
    users: Array,
    roles: Array
  }
};
</script>

<style scoped>
.role-assignment-container {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,138,155,0.07);
  padding: 24px;
  margin-bottom: 24px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  font-size: 1em;
}
th, td {
  border: 1px solid #e4e9f2;
  padding: 10px;
  text-align: left;
}
th {
  background: #f5f7fa;
  color: #008a9b;
  font-weight: bold;
}
tr:nth-child(even) {
  background: #f9fafb;
}
.btn-primary {
  background: linear-gradient(90deg, #008a9b 0%, #ffd600 100%);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 18px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.btn-primary:hover, .btn-primary:focus {
  background: #ffd600;
  color: #008a9b;
}
select {
  padding: 8px;
  border: 1px solid #e4e9f2;
  border-radius: 6px;
  font-size: 1em;
  margin-top: 2px;
  margin-bottom: 8px;
  width: 100%;
  transition: border 0.2s;
}
select:focus {
  border: 1.5px solid #008a9b;
  outline: none;
}
@media (max-width: 600px) {
  .role-assignment-container {
    padding: 8px;
  }
  table, th, td {
    font-size: 0.95em;
    padding: 6px;
  }
}
</style>
