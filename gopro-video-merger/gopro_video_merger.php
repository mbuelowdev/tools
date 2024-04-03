<?php

/**
 * HOW TO USE:
 * 1. Manually download all gopro mp4 files
 * 2. put them into working_dir defined in getConfig()
 * 3. define your $clips in main()
 * 4. ensure you have enough free storage space (mpg files can be x5 as large as mp4)
 *    (the more clips you define the less storage you will need at runtime)
 *    (mpg files will be deleted after conversion to free up space)
 */

/**
 * for zip in *.zip; do unzip $zip; done
 * for vid in GX0*.MP4; do echo "${vid}" >> video_list_norge.txt; done
 * ffmpeg -f concat -safe 0 -i norge_video_list.txt -c copy vacation_2023_june_all_4k.mp4
 */

function getConfig()
{
    // working_dir without trailing slash
    return array(
        'working_dir' => '/mnt/xen_nfs_2/norwegen'
    );
}

function main()
{
    // INTRO:
    // You first have to manually download all your gopro content into the working_dir.
    // Then list all the files in a file separated by newlines. Like this:
    // for vid in *.mp4; do echo "${vid}" >> video_list_norge_mpg.txt; done
    // Now your set.

    // Get the config
    $config = getConfig();

    // Read a file that has each video file in a separate line
    $mpg_video_name_list_content = trim(file_get_contents($config['working_dir'] . '/' . 'video_list_norge.txt'));
    $mpg_video_name_list = explode(PHP_EOL, $mpg_video_name_list_content);
    sort($mpg_video_name_list);

    // Remove the trailing extensions
//    for ($i = 0; $i < count($mpg_video_name_list); $i++) {
//        $mpg_video_name_list[$i] = preg_replace('/.[Mm][Pp]4$/', '', $mpg_video_name_list[$i]);
//        echo $mpg_video_name_list[$i] . PHP_EOL;
//    }
//    die();

    if (count($mpg_video_name_list) === 0) {
        die('Missing files to process');
    }

    if ($config['working_dir'] === '') {
        die('Missing working dir config');
    }

    // Define the resulting clips that should be created by merging all videos from
    // start_video_name to end_video_name.
    // The merge will be inclusive => start_video_name and end_video_name will be
    // in the resulting clip.
    $clips = array(
//        array(
//            'name'             => '2023-06-18-sun',
//            'start_video_name' => 'GX010061',
//            'end_video_name'   => 'GX010105',
//        ),
        array(
            'name'             => '2023-06-19-mon',
            'start_video_name' => 'GX010106',
            'end_video_name'   => 'GX010204',
        ),
        array(
            'name'             => '2023-06-20-tue',
            'start_video_name' => 'GX010205',
            'end_video_name'   => 'GX010275',
        ),
        array(
            'name'             => '2023-06-21-wed',
            'start_video_name' => 'GX010276',
            'end_video_name'   => 'GX010369',
        ),
        array(
            'name'             => '2023-06-22-thu',
            'start_video_name' => 'GX010370',
            'end_video_name'   => 'GX010526',
        ),
        array(
            'name'             => '2023-06-23-fri',
            'start_video_name' => 'GX010527',
            'end_video_name'   => 'GX010635',
        ),
        array(
            'name'             => '2023-06-24-sat',
            'start_video_name' => 'GX010636',
            'end_video_name'   => 'GX010741',
        ),
        array(
            'name'             => '2023-06-25-sun',
            'start_video_name' => 'GX010742',
            'end_video_name'   => 'GX010877',
        ),
        array(
            'name'             => '2023-06-26-mon',
            'start_video_name' => 'GX010878',
            'end_video_name'   => 'GX011016',
        ),
        array(
            'name'             => '2023-06-27-tue',
            'start_video_name' => 'GX011017',
            'end_video_name'   => 'GX011153',
        ),
        array(
            'name'             => '2023-06-28-wed',
            'start_video_name' => 'GX011154',
            'end_video_name'   => 'GX011279',
        ),
        array(
            'name'             => '2023-06-29-thu',
            'start_video_name' => 'GX011280',
            'end_video_name'   => 'GX011298',
        ),
        array(
            'name'             => '2023-06-30-fri',
            'start_video_name' => 'GX011299',
            'end_video_name'   => 'GX011309',
        ),
    );

    foreach ($clips as $clip_property) {
        $video_names = getSublist($mpg_video_name_list, $clip_property['start_video_name'], $clip_property['end_video_name']);

        $finalMpgFile = $config['working_dir'] . '/' . $clip_property['name'] . '.mpg';
        $finalMp4File = $config['working_dir'] . '/' . $clip_property['name'] . '.mp4';

        if (file_exists($finalMp4File)) {
            logi('MP4 file already exists for ' . $clip_property['name']);
            continue;
        }

        // Step 1: Convert to MPG if not done yet

        if (file_exists($finalMpgFile)) {
            logi('Merged MPG file already exists for ' . $finalMpgFile);
        } else {
            foreach ($video_names as $video_name) {
                $filePathMp4 = $config['working_dir'] . '/' . $video_name;
                $filePathMpg = $config['working_dir'] . '/' . $video_name . '.MPG';
                if (file_exists($filePathMpg)) {
                    logi('MPG file already exists for ' . $video_name);
                    continue;
                }


                $command = 'ffmpeg -i ' . $filePathMp4 . ' -qscale 0 ' . $filePathMpg;
                logi('Executing command: ' . $command);
                exec($command);
            }
        }

        // Step 2: Merge MPG files together if not done yet

        if (file_exists($finalMpgFile)) {
            logi('Merged MPG file already exists for ' . $finalMpgFile);
        } else {
            $arrMpgFiles = array();
            foreach ($video_names as $video_name) {
                $arrMpgFiles[] = $config['working_dir'] . '/' . $video_name . '.MPG';
            }

            $command = 'cat ' . implode(' ', $arrMpgFiles) . ' > ' . $finalMpgFile;
            logi('Executing command: ' . $command);
            exec($command);
        }

        // Small sanity check
        if (!file_exists($finalMpgFile) || (filesize($finalMpgFile) === 0)) {
            dieWithProblem('Failed to create MPG file ' . $finalMpgFile);
        }

        // Step 3: Remove single MPG files

        foreach ($video_names as $video_name) {
            $filePathMpg = $config['working_dir'] . '/' . $video_name . '.MPG';
            $command = 'rm ' . $filePathMpg;
            logi('Executing command: ' . $command);
            exec($command);
        }

        // Step 4: Convert to mp4

        $command = 'ffmpeg -threads 16 -i ' . $finalMpgFile . ' -threads 16 -qscale 0 -vcodec libx264 ' . $finalMp4File;
        logi('Executing command: ' . $command);
        exec($command);

        // Small sanity check
        if (!file_exists($finalMp4File) || (filesize($finalMp4File) === 0)) {
            dieWithProblem('Failed to create MP4 file ' . $finalMp4File);
        }

        // Step 5: remove merged MPG file

        $command = 'rm ' . $finalMpgFile;
        logi('Executing command: ' . $command);
        exec($command);

        notify('Successfully created ' . $finalMp4File . ' with size of ' . filesize($finalMp4File));
    }
}

function logi($message)
{
    echo $message . PHP_EOL;
}

function dieWithProblem($message)
{
    notify($message);
    die();
}

function notify($message)
{
    $message = str_replace('"', '\"', $message);
    $command = 'notify "' . $message . '"';
    logi('Exiting and executing command: ' . $command);
    exec($command);
    sleep(1);
}

function getSublist($all, $start_video, $end_video)
{
    $sublist = array();
    $is_inside = false;

    foreach ($all as $video) {
        if ($video === ($start_video . '.MP4')) {
            $is_inside = true;
        }

        if ($is_inside) {
            $sublist[] = $video;
        }

        if ($video === ($end_video . '.MP4')) {
            $is_inside = false;
        }
    }

    return $sublist;
}

main();
