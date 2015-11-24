import pymysql

def aggregate_classifications(photo_ID):
    connection = pymysql.connect(host='localhost',user='root',password='toot',db='mammalwebdump',charset='utf8mb4',cursorclass=pymysql.cursors.DictCursor)
    c=connection.cursor()
    sql="SELECT * FROM animal where photo_id="+str(photo_ID)
    c.execute(sql)
    result=c.fetchall()
    
    speciesTally=dict()
    for row in result:
        if row['species'] in speciesTally:
            speciesTally[row['species']]=speciesTally[row['species']]+1
        else:
            speciesTally[row['species']]=1
    values=list(speciesTally.values())
    keys=list(speciesTally.keys())
    species=keys[values.index(max(values))]
    print(species)
        
    sql="INSERT INTO nameofaggregateclasstablehere (%s,%s,%s,etc)"
    #c.execute(sql,(values (sepcies, evenness etc) here))
    #c.commit()
    connection.close()
