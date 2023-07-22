import React, { useState } from "react";
import axios from "axios";

const Anwendung = () => {
  const [email, setEmail] = useState("");
  const [selectedFiles, setSelectedFiles] = useState([]);

  const handleEmailChange = (e) => {
    setEmail(e.target.value);
  };

  const handleFilesChange = (e) => {
    setSelectedFiles([...e.target.files]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    console.log(selectedFiles);
    if (selectedFiles.length !== 2) {
      console.error("Es müssen zwei Dateien hochgeladen werden: JSON und Bild.");
      return;
    }

    const formData = new FormData();
    formData.append("email", email);

    // Loop through selected files and append them to formData
    for (let i = 0; i < selectedFiles.length; i++) {
      formData.append("files[]", selectedFiles[i]);
    }

    try {
      const response = await axios.post("/upload", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      console.log("Response from Laravel:", response.data);

      // Reset form fields after successful submission
      setEmail("");
      setSelectedFiles([]);
    } catch (error) {
      console.error("Error submitting form:", error);
    }
  };

  return (
    <div>
      <h1>Multi-Upload Form</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:</label>
          <input type="email" value={email} onChange={handleEmailChange} required />
        </div>
        <div>
          <label>Upload Files (JSON and Image, PNG or JPG):</label>
          <input
            type="file"
            accept=".json, .png, .jpg, .jpeg"
            multiple
            onChange={handleFilesChange}
            required
          />
        </div>
        <button type="submit">Submit</button>
      </form>
    </div>
  );
};

export default Anwendung;



