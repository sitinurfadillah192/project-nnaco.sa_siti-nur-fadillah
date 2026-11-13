<?php
class Category extends Model {
    protected $table = 'categories';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (name, slug, description) VALUES (:name, :slug, :description)";
        $this->db->query($query);
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateCategory($id, $data) {
        $query = "UPDATE " . $this->table . " SET name = :name, slug = :slug, description = :description WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
