## A simple script for preprocessing the dwca files for ingestion. This script was needed because there were a few discrepancies between how symbiota handles data and how harvard's dwca files are packaged.

import zipfile
import csv
import sys
import codecs
from tempfile import NamedTemporaryFile, mkdtemp
import os
import shutil
from tqdm import tqdm

def fix_associatedMedia(zip_file_path, only_keep_records_with_images):
    tempfile = NamedTemporaryFile(mode='w', delete=False, newline='')
    temp_dir = mkdtemp()
    # Extract multimedia links from the multimedia file in the ZIP archive
    url_list = {} # key: id, value: image url
    with zipfile.ZipFile(zip_file_path, 'r') as zip_ref:
        with zip_ref.open("multimedia.txt", 'r') as multimedia_csv:
            csv_reader = csv.DictReader(codecs.iterdecode(multimedia_csv, 'utf-8'), delimiter="\t")
            for row in tqdm(csv_reader, desc="Reading media links from multimedia.txt"):
                url_list[row['id']] = row['identifier']
    # Write the links into a temporary occurrence file
    with zipfile.ZipFile(zip_file_path, 'r') as zip_ref:
        with zip_ref.open("occurrence.txt", 'r') as occurrence_csv:
            csv_reader = csv.DictReader(codecs.iterdecode(occurrence_csv, 'utf-8'), delimiter="\t")
            fields = csv_reader.fieldnames
            csv_writer = csv.DictWriter(tempfile, delimiter="\t", fieldnames=fields)
            csv_writer.writeheader()
            for row in tqdm(csv_reader, desc="Updating occurrence.txt with media links"):
                if row['id'] in url_list:
                    row['associatedMedia'] = url_list[row['id']]
                    try:
                        csv_writer.writerow(row)
                    except Exception as e:
                        print(f"Skipped row {row['id']}\n")
                else:
                    if not only_keep_records_with_images:
                        try:
                            csv_writer.writerow(row)
                        except Exception as e:
                            print(f"Skipped row {row['id']}\n")
            tempfile.close()
    # create a copy of the zip archive without the original occurrence.txt
    print("Creating new zip archive...")
    with zipfile.ZipFile(zip_file_path, 'r') as zip_ref:
        zip_ref.extractall(temp_dir)
        os.remove(os.path.join(temp_dir,"occurrence.txt"))
    # rezip with new occurrence.txt
    print("Rezipping file with new occurrence.txt...")
    basename, _ = os.path.splitext(zip_file_path)
    new_path = basename + "-preprocessed"
    shutil.make_archive(new_path, 'zip', temp_dir)
    new_zip_path = new_path + ".zip"
    with zipfile.ZipFile(new_zip_path, 'a') as zip_ref:
        zip_ref.write(tempfile.name, "occurrence.txt")
    print("Success: " + new_zip_path)
    # change guid, add issue regarding update occurrence for images upon ingestion

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python [preprocess-script.py] [path to dwca zip file] [only keep records with images (Y/N)]")
        sys.exit(1)
    
    zip_file_path = sys.argv[1]
    arg2 = sys.argv[2]
    if arg2 == "Y":
        only_keep_records_with_images = True
    elif arg2 == "N":
        only_keep_records_with_images = False
    else:
        print("Please only enter Y/N to indicate whether to only keep records with images")
        sys.exit(1)
    fix_associatedMedia(zip_file_path, only_keep_records_with_images)