<?php

namespace App\Controllers;

use App\Models\EventModel;
use CodeIgniter\RESTful\ResourceController;
use DateTime;

class Event extends ResourceController
{

    /**
     * Return an array of events objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $model = new EventModel();
        $data['events'] = $model->orderBy('start',)->findAll();
        return $this->respond($data);
    }


    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $model = new EventModel();
        $data = $model->where('id', $id)->first();
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('Data event tidak ditemukan.');
        }
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $model = new EventModel();
        $start = new DateTime($this->request->getVar('start'));
        $end = new DateTime($this->request->getVar('end'));
        $now = new DateTime();

        // mengambil data gambar
        $image = $this->request->getFile('image');

        // cek apakah file yang diupload adalah gambar
        if ($image) {
            // allowed file image
            $allowedFile = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($image->getClientMimeType(), $allowedFile)) {
                return $this->failValidationErrors('File yang diupload bukan gambar');
            }
            $imageUrl = $image->getRandomName();

            $image->move('uploads/images', $imageUrl); // memindah gambar ke folder public
        } else {
            $imageUrl = 'default.jpg';
        }


        if ($start >= $end || $start <= $now) {
            return $this->failForbidden('Tanggal tidak valid.');
        }
        // echo $start->diff($end)->format("%a hari %H jam %i menit");

        $data = [
            'title' => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'start' => $this->request->getVar('start'),
            'end' => $this->request->getVar('end'),
            'venue' => $this->request->getVar('venue'),
            'price' => $this->request->getVar('price'),
            'organizer' => $this->request->getVar('organizer'),
            'image_url' => $imageUrl,
        ];
        $model->insert($data);

        $response = [
            'status' => 201,
            'error' => null,
            'messages' => [
                'success' => 'Event berhasil ditambahkan'
            ]
        ];
        return $this->respondCreated($response);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        // 
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $model = new EventModel();
        $start = new DateTime($this->request->getVar('start'));
        $end = new DateTime($this->request->getVar('end'));
        $now = new DateTime();

        // ambil data sebelumnya
        $prevData = $model->where('id', $id)->first();

        // mengambil data gambar
        $image = $this->request->getFile('image');

        // cek apakah file yang diupload adalah gambar
        if ($image) {
            // allowed file image
            $allowedFile = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($image->getClientMimeType(), $allowedFile)) {
                return $this->failValidationErrors('File yang diupload bukan gambar');
            }
            $imageUrl = $image->getRandomName();

            // cari dan hapus gambar sebelumnya
            if ($prevData['image_url'] != 'default.jpg') {
                unlink('uploads/images/' . $prevData['image_url']);
            }

            $image->move('uploads/images', $imageUrl); // memindah gambar ke folder public
        } else {
            $imageUrl = $prevData['image_url'];
        }


        if ($start >= $end || $start <= $now) {
            return $this->failForbidden('Tanggal tidak valid.');
        }

        $data = [
            'title' => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'start' => $this->request->getVar('start'),
            'end' => $this->request->getVar('end'),
            'venue' => $this->request->getVar('venue'),
            'price' => $this->request->getVar('price'),
            'organizer' => $this->request->getVar('organizer'),
            'image_url' => $imageUrl,
        ];
        $model->where('id', $id)->set($data)->update();

        $response = [
            'status' => 200,
            'error' => null,
            'messages' => [
                'success' => 'Event berhasil diubah'
            ]
        ];
        return $this->respondUpdated($response);
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $model = new EventModel();
        $data = $model->where('id', $id)->first();
        if ($data) {
            $deleted = $model->delete($id);
            return $this->respondDeleted($deleted);
        }
        return $this->failNotFound('Data tidak ditemukan.');
    }
}
